import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:printing/printing.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:sixam_mart_delivery/features/order/domain/models/b2b_invoice_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_model.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_snackbar_widget.dart';
import 'package:sixam_mart_delivery/util/dimensions.dart';
import 'package:sixam_mart_delivery/util/styles.dart';
import 'package:url_launcher/url_launcher.dart';

class B2BInvoiceScreen extends StatefulWidget {
  final int orderId;
  final String token;
  final OrderModel? orderData; // Add optional order data

  const B2BInvoiceScreen({
    Key? key,
    required this.orderId,
    required this.token,
    this.orderData, // Optional order data
  }) : super(key: key);

  @override
  State<B2BInvoiceScreen> createState() => _B2BInvoiceScreenState();
}

class _B2BInvoiceScreenState extends State<B2BInvoiceScreen> {
  B2BOrder? _order;
  bool _isLoading = true;
  bool _isDownloading = false;
  bool _isPrinting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadOrderDetails();
  }

  Future<void> _loadOrderDetails() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      B2BOrder? order;

      // If order data is provided, use it directly
      if (widget.orderData != null) {
        print('Using provided order data for invoice');
        order = B2BInvoiceService.convertOrderToB2BOrder(widget.orderData);
        print('Converted order: ${order != null ? 'Success' : 'Failed'}');
      } else {
        // Otherwise, try to fetch from API
        print('Fetching order data from API');
        order = await B2BInvoiceService.getOrderDetails(
          widget.orderId,
          widget.token,
        );
        print('API order: ${order != null ? 'Success' : 'Failed'}');
      }

      setState(() {
        _order = order;
        _isLoading = false;
      });

      if (order == null) {
        setState(() {
          _error = 'Failed to load order details';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _downloadInvoice() async {
    if (_order == null) return;

    try {
      setState(() {
        _isDownloading = true;
      });

      // Request storage permission
      final status = await Permission.storage.request();
      if (!status.isGranted) {
        showCustomSnackBar(
            'Storage permission is required to download invoice');
        return;
      }

      final bytes = await B2BInvoiceService.downloadInvoice(
        widget.orderId,
        widget.token,
      );

      if (bytes != null) {
        final directory = await getExternalStorageDirectory();
        final fileName = 'Order_${widget.orderId}_Invoice.pdf';
        final file = File('${directory!.path}/$fileName');
        await file.writeAsBytes(bytes);

        showCustomSnackBar(
          'Invoice downloaded successfully to: ${file.path}',
        );
      } else {
        showCustomSnackBar('Failed to download invoice');
      }
    } catch (e) {
      showCustomSnackBar('Error downloading invoice: $e');
    } finally {
      setState(() {
        _isDownloading = false;
      });
    }
  }

  Future<void> _printInvoice() async {
    if (_order == null) return;

    try {
      setState(() {
        _isPrinting = true;
      });

      // Generate PDF
      final pdf = await _generateInvoicePDF();

      // Print the PDF
      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async => pdf,
        name: 'Invoice_Order_${_order!.id}',
      );

      showCustomSnackBar('Invoice sent to printer successfully');
    } catch (e) {
      showCustomSnackBar('Error printing invoice: $e');
    } finally {
      setState(() {
        _isPrinting = false;
      });
    }
  }

  Future<Uint8List> _generateInvoicePDF() async {
    final pdf = pw.Document();

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              _buildPDFHeader(),
              pw.SizedBox(height: 20),
              _buildPDFOrderInfo(),
              pw.SizedBox(height: 20),
              _buildPDFCustomerInfo(),
              pw.SizedBox(height: 20),
              _buildPDFOrderItems(),
              pw.SizedBox(height: 20),
              _buildPDFOrderSummary(),
              pw.SizedBox(height: 20),
              _buildPDFPaymentInfo(),
              pw.SizedBox(height: 20),
              _buildPDFThankYou(),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  pw.Widget _buildPDFHeader() {
    return pw.Column(
      children: [
        // Store Header
        if (_order!.store != null) ...[
          pw.Center(
            child: pw.Text(
              _order!.store!.name,
              style: pw.TextStyle(
                fontSize: 18,
                fontWeight: pw.FontWeight.bold,
              ),
            ),
          ),
          pw.SizedBox(height: 8),
          pw.Center(
            child: pw.Text(
              _order!.store!.address,
              style: pw.TextStyle(fontSize: 12),
            ),
          ),
          pw.SizedBox(height: 4),
          pw.Center(
            child: pw.Text(
              'Phone: ${_order!.store!.phone}',
              style: pw.TextStyle(fontSize: 12),
            ),
          ),
          pw.SizedBox(height: 16),
        ],

        // Receipt Header
        pw.Row(
          children: [
            pw.Expanded(child: pw.Text('★ ★ ★')),
            pw.Text(
              'CASH RECEIPT',
              style: pw.TextStyle(
                fontSize: 16,
                fontWeight: pw.FontWeight.bold,
              ),
            ),
            pw.Expanded(child: pw.Text('★ ★ ★')),
          ],
        ),
      ],
    );
  }

  pw.Widget _buildPDFOrderInfo() {
    return pw.Column(
      children: [
        pw.Center(
          child: pw.Text(
            'Order ID: ${_order!.id}',
            style: pw.TextStyle(fontWeight: pw.FontWeight.bold),
          ),
        ),
        pw.SizedBox(height: 4),
        pw.Center(
          child: pw.Text(
            InvoiceUtils.formatDate(_order!.createdAt),
            style: pw.TextStyle(fontSize: 12),
          ),
        ),
        if (_order!.store?.gstStatus == true &&
            _order!.store?.gstCode != null) ...[
          pw.SizedBox(height: 4),
          pw.Center(
            child: pw.Text(
              'GST No: ${_order!.store!.gstCode}',
              style: pw.TextStyle(fontSize: 12),
            ),
          ),
        ],
      ],
    );
  }

  pw.Widget _buildPDFCustomerInfo() {
    final deliveryAddress =
        InvoiceUtils.parseDeliveryAddress(_order!.deliveryAddress);

    return pw.Column(
      crossAxisAlignment: pw.CrossAxisAlignment.start,
      children: [
        pw.Text(
          'Contact Information',
          style: pw.TextStyle(
            fontSize: 14,
            fontWeight: pw.FontWeight.bold,
          ),
        ),
        pw.SizedBox(height: 8),
        if (deliveryAddress != null) ...[
          pw.Text(
              'Contact Name: ${deliveryAddress['contact_person_name'] ?? ''}'),
          pw.Text('Phone: ${deliveryAddress['contact_person_number'] ?? ''}'),
          pw.Text('Address: ${deliveryAddress['address'] ?? ''}'),
        ] else if (_order!.customer != null) ...[
          pw.Text('Contact Name: ${_order!.customer!.fullName}'),
          pw.Text('Phone: ${_order!.customer!.phone}'),
        ],
      ],
    );
  }

  pw.Widget _buildPDFOrderItems() {
    return pw.Column(
      children: [
        // Table Header
        pw.Container(
          padding: const pw.EdgeInsets.all(8),
          decoration: pw.BoxDecoration(
            border: pw.Border.all(),
          ),
          child: pw.Row(
            children: [
              pw.Expanded(
                flex: 3,
                child: pw.Text('Description'),
              ),
              pw.Expanded(
                flex: 1,
                child: pw.Text('Qty', textAlign: pw.TextAlign.center),
              ),
              pw.Expanded(
                flex: 2,
                child: pw.Text('Price', textAlign: pw.TextAlign.right),
              ),
            ],
          ),
        ),
        // Order Items
        ..._order!.details.map((detail) => _buildPDFOrderItem(detail)),
      ],
    );
  }

  pw.Widget _buildPDFOrderItem(B2BOrderDetail detail) {
    final itemName = detail.itemDetails['name'] ?? 'Unknown Item';

    return pw.Container(
      padding: const pw.EdgeInsets.all(8),
      decoration: pw.BoxDecoration(
        border: pw.Border(
          bottom: pw.BorderSide(),
        ),
      ),
      child: pw.Column(
        crossAxisAlignment: pw.CrossAxisAlignment.start,
        children: [
          pw.Row(
            children: [
              pw.Expanded(
                flex: 3,
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text(
                      itemName,
                      style: pw.TextStyle(fontWeight: pw.FontWeight.bold),
                    ),
                    if (detail.variation.isNotEmpty) ...[
                      pw.SizedBox(height: 4),
                      _buildPDFVariations(detail.variation),
                    ],
                    if (detail.addOns.isNotEmpty) ...[
                      pw.SizedBox(height: 4),
                      _buildPDFAddOns(detail.addOns),
                    ],
                  ],
                ),
              ),
              pw.Expanded(
                flex: 1,
                child: pw.Text(
                  detail.quantity.toStringAsFixed(2),
                  textAlign: pw.TextAlign.center,
                ),
              ),
              pw.Expanded(
                flex: 2,
                child: pw.Text(
                  InvoiceUtils.formatCurrency(detail.totalAmount),
                  textAlign: pw.TextAlign.right,
                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  pw.Widget _buildPDFVariations(List<dynamic> variations) {
    return pw.Column(
      crossAxisAlignment: pw.CrossAxisAlignment.start,
      children: [
        pw.Text(
          'Variations:',
          style: pw.TextStyle(
            fontWeight: pw.FontWeight.bold,
            decoration: pw.TextDecoration.underline,
          ),
        ),
        ...variations.map((variation) {
          if (variation is Map<String, dynamic>) {
            if (variation.containsKey('name') &&
                variation.containsKey('values')) {
              return pw.Column(
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: [
                  pw.Text('${variation['name']}:'),
                  ...(variation['values'] as List).map((value) => pw.Padding(
                        padding: const pw.EdgeInsets.only(left: 16),
                        child: pw.Text(
                            '${value['label']}: ${InvoiceUtils.formatCurrency(value['optionPrice'])}'),
                      )),
                ],
              );
            } else {
              return pw.Column(
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: variation.entries.map((entry) {
                  if (entry.key != 'stock') {
                    return pw.Text('${entry.key}: ${entry.value}');
                  }
                  return pw.SizedBox.shrink();
                }).toList(),
              );
            }
          }
          return pw.SizedBox.shrink();
        }),
      ],
    );
  }

  pw.Widget _buildPDFAddOns(List<dynamic> addOns) {
    return pw.Column(
      crossAxisAlignment: pw.CrossAxisAlignment.start,
      children: [
        pw.Text(
          'Add-ons:',
          style: pw.TextStyle(
            fontWeight: pw.FontWeight.bold,
            decoration: pw.TextDecoration.underline,
          ),
        ),
        ...addOns.map((addon) => pw.Padding(
              padding: const pw.EdgeInsets.only(left: 16),
              child: pw.Text(
                  '${addon['name']}: ${addon['quantity']} x ${InvoiceUtils.formatCurrency(addon['price'])}'),
            )),
      ],
    );
  }

  pw.Widget _buildPDFOrderSummary() {
    double subtotal = 0;
    double addOnsCost = 0;
    double totalTax = 0;

    // Calculate totals
    for (var detail in _order!.details) {
      subtotal += detail.totalAmount;
      totalTax += detail.taxAmount * detail.quantity;

      // Calculate add-ons cost
      for (var addon in detail.addOns) {
        addOnsCost += addon['price'] * addon['quantity'];
      }
    }

    final totalDiscount = _order!.storeDiscountAmount +
        _order!.couponDiscountAmount +
        _order!.refBonusAmount;

    return pw.Column(
      children: [
        _buildPDFSummaryRow('Subtotal', subtotal + addOnsCost,
            suffix: _order!.taxStatus == 'included' ? ' (TAX Included)' : ''),
        _buildPDFSummaryRow('Discount', -totalDiscount),
        _buildPDFSummaryRow('Coupon Discount', -_order!.couponDiscountAmount),
        if (_order!.refBonusAmount > 0)
          _buildPDFSummaryRow('Referral Discount', -_order!.refBonusAmount),
        if (_order!.taxStatus == 'excluded' || _order!.taxStatus == null)
          _buildPDFSummaryRow('VAT/Tax', _order!.totalTaxAmount, prefix: '+'),
        _buildPDFSummaryRow('Delivery Man Tips', _order!.dmTips, prefix: '+'),
        _buildPDFSummaryRow('Delivery Charge', _order!.deliveryCharge),
        _buildPDFSummaryRow('Additional Charge', _order!.additionalCharge,
            prefix: '+'),
        if (_order!.extraPackagingAmount > 0)
          _buildPDFSummaryRow(
              'Extra Packaging Amount', _order!.extraPackagingAmount,
              prefix: '+'),
        pw.Divider(thickness: 2),
        _buildPDFSummaryRow('Total', _order!.orderAmount, isTotal: true),
      ],
    );
  }

  pw.Widget _buildPDFSummaryRow(String label, double amount,
      {String prefix = '', String suffix = '', bool isTotal = false}) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 2),
      child: pw.Row(
        children: [
          pw.Expanded(
            flex: 3,
            child: pw.Text(
              '$label$suffix:',
              style:
                  isTotal ? pw.TextStyle(fontWeight: pw.FontWeight.bold) : null,
            ),
          ),
          pw.Expanded(
            flex: 2,
            child: pw.Text(
              '$prefix${InvoiceUtils.formatCurrency(amount)}',
              textAlign: pw.TextAlign.right,
              style:
                  isTotal ? pw.TextStyle(fontWeight: pw.FontWeight.bold) : null,
            ),
          ),
        ],
      ),
    );
  }

  pw.Widget _buildPDFPaymentInfo() {
    if (_order!.payments == null || _order!.payments!.isEmpty)
      return pw.SizedBox.shrink();

    return pw.Column(
      children: _order!.payments!.map((payment) {
        String label;
        if (payment.paymentStatus == 'paid') {
          if (payment.paymentMethod == 'cash_on_delivery') {
            label = 'Paid with Cash (COD)';
          } else {
            label = 'Paid by ${payment.paymentMethod}';
          }
        } else {
          label =
              'Due Amount (${payment.paymentMethod == 'cash_on_delivery' ? 'COD' : payment.paymentMethod})';
        }

        return _buildPDFSummaryRow(label, payment.amount);
      }).toList(),
    );
  }

  pw.Widget _buildPDFThankYou() {
    return pw.Column(
      children: [
        pw.Row(
          children: [
            pw.Expanded(child: pw.Text('★ ★ ★')),
            pw.Text(
              'THANK YOU',
              style: pw.TextStyle(
                fontSize: 16,
                fontWeight: pw.FontWeight.bold,
              ),
            ),
            pw.Expanded(child: pw.Text('★ ★ ★')),
          ],
        ),
        pw.SizedBox(height: 8),
        pw.Center(
          child: pw.Text(
            '© NutriBasket. All rights reserved.',
            style: pw.TextStyle(fontSize: 10),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBarWidget(
        title: 'Invoice',
        actionWidget: _order != null
            ? IconButton(
                icon: const Icon(Icons.download),
                onPressed: _isDownloading ? null : _downloadInvoice,
              )
            : null,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorWidget()
              : _order != null
                  ? _buildThermalInvoiceContent()
                  : _buildErrorWidget(),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.red,
          ),
          const SizedBox(height: 16),
          Text(
            _error ?? 'Failed to load invoice',
            style: robotoMedium.copyWith(fontSize: 16),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadOrderDetails,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildThermalInvoiceContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        children: [
          _buildPrintButton(),
          const SizedBox(height: 16),
          _buildThermalInvoice(),
        ],
      ),
    );
  }

  Widget _buildPrintButton() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          ElevatedButton.icon(
            onPressed: _isPrinting ? null : _printInvoice,
            icon: _isPrinting
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.print),
            label: Text(_isPrinting ? 'Printing...' : 'Print Invoice'),
          ),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.arrow_back),
            label: const Text('Back'),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
          ),
        ],
      ),
    );
  }

  Widget _buildThermalInvoice() {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: Colors.black),
        borderRadius: BorderRadius.circular(8),
        color: Colors.white,
      ),
      child: Column(
        children: [
          _buildStoreHeader(),
          _buildReceiptHeader(),
          _buildOrderInfo(),
          _buildCustomerInfo(),
          _buildOrderItems(),
          _buildOrderSummary(),
          _buildPaymentInfo(),
          _buildThankYouSection(),
        ],
      ),
    );
  }

  Widget _buildStoreHeader() {
    if (_order!.store == null) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Store Logo Placeholder
          Container(
            height: 60,
            width: 60,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.store, size: 30),
          ),
          const SizedBox(height: 8),
          // Store Name
          Text(
            _order!.store!.name,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          // Store Address
          Text(
            _order!.store!.address,
            style: const TextStyle(fontSize: 14),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          // Store Phone
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('Phone: '),
              Text(_order!.store!.phone),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildReceiptHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Decorative stars
          Row(
            children: [
              Expanded(child: _buildStarDecoration()),
              const SizedBox(width: 8),
              const Text(
                'CASH RECEIPT',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(child: _buildStarDecoration()),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStarDecoration() {
    return Row(
      children: List.generate(
          3, (index) => const Text('★', style: TextStyle(fontSize: 12))),
    );
  }

  Widget _buildOrderInfo() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Order ID
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('Order ID: '),
              Text(
                _order!.id.toString(),
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 4),
          // Order Date
          Text(InvoiceUtils.formatDate(_order!.createdAt)),
          const SizedBox(height: 4),
          // GST Number (if available)
          if (_order!.store?.gstStatus == true &&
              _order!.store?.gstCode != null)
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('GST No: '),
                Text(_order!.store!.gstCode!),
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildCustomerInfo() {
    final deliveryAddress =
        InvoiceUtils.parseDeliveryAddress(_order!.deliveryAddress);

    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Contact Information',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          if (deliveryAddress != null) ...[
            Row(
              children: [
                const Text('Contact Name: '),
                Text(deliveryAddress['contact_person_name'] ?? ''),
              ],
            ),
            Row(
              children: [
                const Text('Phone: '),
                Text(deliveryAddress['contact_person_number'] ?? ''),
              ],
            ),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Address: '),
                Expanded(
                  child: Text(deliveryAddress['address'] ?? ''),
                ),
              ],
            ),
          ] else if (_order!.customer != null) ...[
            Row(
              children: [
                const Text('Contact Name: '),
                Text(_order!.customer!.fullName),
              ],
            ),
            Row(
              children: [
                const Text('Phone: '),
                Text(_order!.customer!.phone),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildOrderItems() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Table Header
          Container(
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
            decoration: BoxDecoration(
              border: Border.all(color: Colors.black),
            ),
            child: const Row(
              children: [
                Expanded(flex: 3, child: Text('Description')),
                Expanded(
                    flex: 1, child: Text('Qty', textAlign: TextAlign.center)),
                Expanded(
                    flex: 2, child: Text('Price', textAlign: TextAlign.right)),
              ],
            ),
          ),
          // Order Items
          ..._order!.details.map((detail) => _buildOrderItem(detail)),
        ],
      ),
    );
  }

  Widget _buildOrderItem(B2BOrderDetail detail) {
    final itemName = detail.itemDetails['name'] ?? 'Unknown Item';

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: Colors.grey[300]!),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                flex: 3,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      itemName,
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    if (detail.variation.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      _buildVariations(detail.variation),
                    ],
                    if (detail.addOns.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      _buildAddOns(detail.addOns),
                    ],
                  ],
                ),
              ),
              Expanded(
                flex: 1,
                child: Text(
                  detail.quantity.toStringAsFixed(2),
                  textAlign: TextAlign.center,
                ),
              ),
              Expanded(
                flex: 2,
                child: Text(
                  InvoiceUtils.formatCurrency(detail.totalAmount),
                  textAlign: TextAlign.right,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildVariations(List<dynamic> variations) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Variations:',
          style: TextStyle(
              fontWeight: FontWeight.bold,
              decoration: TextDecoration.underline),
        ),
        ...variations.map((variation) {
          if (variation is Map<String, dynamic>) {
            if (variation.containsKey('name') &&
                variation.containsKey('values')) {
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('${variation['name']}:'),
                  ...(variation['values'] as List).map((value) => Padding(
                        padding: const EdgeInsets.only(left: 16),
                        child: Text(
                            '${value['label']}: ${InvoiceUtils.formatCurrency(value['optionPrice'])}'),
                      )),
                ],
              );
            } else {
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: variation.entries.map((entry) {
                  if (entry.key != 'stock') {
                    return Text('${entry.key}: ${entry.value}');
                  }
                  return const SizedBox.shrink();
                }).toList(),
              );
            }
          }
          return const SizedBox.shrink();
        }),
      ],
    );
  }

  Widget _buildAddOns(List<dynamic> addOns) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Add-ons:',
          style: TextStyle(
              fontWeight: FontWeight.bold,
              decoration: TextDecoration.underline),
        ),
        ...addOns.map((addon) => Padding(
              padding: const EdgeInsets.only(left: 16),
              child: Text(
                  '${addon['name']}: ${addon['quantity']} x ${InvoiceUtils.formatCurrency(addon['price'])}'),
            )),
      ],
    );
  }

  Widget _buildOrderSummary() {
    double subtotal = 0;
    double addOnsCost = 0;
    double totalTax = 0;

    // Calculate totals
    for (var detail in _order!.details) {
      subtotal += detail.totalAmount;
      totalTax += detail.taxAmount * detail.quantity;

      // Calculate add-ons cost
      for (var addon in detail.addOns) {
        addOnsCost += addon['price'] * addon['quantity'];
      }
    }

    final totalDiscount = _order!.storeDiscountAmount +
        _order!.couponDiscountAmount +
        _order!.refBonusAmount;

    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildSummaryRow('Subtotal', subtotal + addOnsCost,
              suffix: _order!.taxStatus == 'included' ? ' (TAX Included)' : ''),
          _buildSummaryRow('Discount', -totalDiscount),
          _buildSummaryRow('Coupon Discount', -_order!.couponDiscountAmount),
          if (_order!.refBonusAmount > 0)
            _buildSummaryRow('Referral Discount', -_order!.refBonusAmount),
          if (_order!.taxStatus == 'excluded' || _order!.taxStatus == null)
            _buildSummaryRow('VAT/Tax', _order!.totalTaxAmount, prefix: '+'),
          _buildSummaryRow('Delivery Man Tips', _order!.dmTips, prefix: '+'),
          _buildSummaryRow('Delivery Charge', _order!.deliveryCharge),
          _buildSummaryRow('Additional Charge', _order!.additionalCharge,
              prefix: '+'),
          if (_order!.extraPackagingAmount > 0)
            _buildSummaryRow(
                'Extra Packaging Amount', _order!.extraPackagingAmount,
                prefix: '+'),
          const Divider(thickness: 2),
          _buildSummaryRow('Total', _order!.orderAmount, isTotal: true),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, double amount,
      {String prefix = '', String suffix = '', bool isTotal = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Expanded(
            flex: 3,
            child: Text(
              '$label$suffix:',
              style:
                  isTotal ? const TextStyle(fontWeight: FontWeight.bold) : null,
            ),
          ),
          Expanded(
            flex: 2,
            child: Text(
              '$prefix${InvoiceUtils.formatCurrency(amount)}',
              textAlign: TextAlign.right,
              style:
                  isTotal ? const TextStyle(fontWeight: FontWeight.bold) : null,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentInfo() {
    if (_order!.payments == null || _order!.payments!.isEmpty)
      return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: _order!.payments!.map((payment) {
          String label;
          if (payment.paymentStatus == 'paid') {
            if (payment.paymentMethod == 'cash_on_delivery') {
              label = 'Paid with Cash (COD)';
            } else {
              label = 'Paid by ${payment.paymentMethod}';
            }
          } else {
            label =
                'Due Amount (${payment.paymentMethod == 'cash_on_delivery' ? 'COD' : payment.paymentMethod})';
          }

          return _buildSummaryRow(label, payment.amount);
        }).toList(),
      ),
    );
  }

  Widget _buildThankYouSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(child: _buildStarDecoration()),
              const SizedBox(width: 8),
              const Text(
                'THANK YOU',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(child: _buildStarDecoration()),
            ],
          ),
          const SizedBox(height: 8),
          const Text(
            '© NutriBasket. All rights reserved.',
            style: TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
