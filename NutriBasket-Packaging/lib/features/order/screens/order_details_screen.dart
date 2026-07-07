import 'dart:async';
import 'package:sixam_mart_delivery/features/order/controllers/order_controller.dart';
import 'package:sixam_mart_delivery/features/profile/controllers/profile_controller.dart';
import 'package:sixam_mart_delivery/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_model.dart';
import 'package:sixam_mart_delivery/helper/route_helper.dart';
import 'package:sixam_mart_delivery/util/dimensions.dart';
import 'package:sixam_mart_delivery/util/styles.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_snackbar_widget.dart';
import 'package:sixam_mart_delivery/features/order/screens/b2b_invoice_screen.dart';
import 'package:sixam_mart_delivery/features/auth/controllers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

class OrderDetailsScreen extends StatefulWidget {
  final int? orderId;
  final bool? isRunningOrder;
  final int? orderIndex;
  final bool fromNotification;
  final bool fromLocationScreen;
  const OrderDetailsScreen(
      {super.key,
      required this.orderId,
      required this.isRunningOrder,
      required this.orderIndex,
      this.fromNotification = false,
      this.fromLocationScreen = false});

  @override
  State<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen>
    with WidgetsBindingObserver, TickerProviderStateMixin {
  Timer? _timer;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;

  Future<void> _loadData() async {
    Get.find<OrderController>()
        .pickPrescriptionImage(isRemove: true, isCamera: false);
    await Get.find<OrderController>().getPackagerOrderDetails(widget.orderId!);
    await Get.find<OrderController>().getLatestOrders();
    if (Get.find<OrderController>().showDeliveryImageField) {
      Get.find<OrderController>().changeDeliveryImageStatus(isUpdate: false);
    }
  }

  Color _getStatusColor(String? status) {
    switch (status?.toLowerCase()) {
      case 'pending':
        return const Color(0xFFE84D4F);
      case 'confirmed':
        return const Color(0xFF2A9849);
      case 'processing':
        return const Color(0xFF1ED7AA);
      case 'packaging':
        return const Color(0xFF1ED7AA);
      case 'picked_up':
        return const Color(0xFF2A9849);
      case 'out_for_delivery':
        return const Color(0xFF2A9849);
      case 'delivered':
        return const Color(0xFF2A9849);
      case 'cancelled':
        return const Color(0xFFE84D4F);
      default:
        return const Color(0xFFA0A4A8);
    }
  }

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );

    WidgetsBinding.instance.addObserver(this);
    _loadData();
    _animationController.forward();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    super.didChangeAppLifecycleState(state);
    if (state == AppLifecycleState.paused) {}
  }

  @override
  void dispose() {
    _animationController.dispose();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope<Object?>(
      canPop: true,
      onPopInvokedWithResult: (didPop, result) async {
        if ((widget.fromNotification || widget.fromLocationScreen)) {
          Future.delayed(const Duration(milliseconds: 0), () async {
            await Get.offAllNamed(RouteHelper.getInitialRoute());
          });
        } else {
          return;
        }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF8F9FA),
        appBar: CustomAppBarWidget(
            title: 'Order Details',
            onBackPressed: () {
              if (widget.fromNotification || widget.fromLocationScreen) {
                Get.offAllNamed(RouteHelper.getInitialRoute());
              } else {
                Get.back();
              }
            }),
        body: FadeTransition(
          opacity: _fadeAnimation,
          child: GetBuilder<OrderController>(builder: (orderController) {
            OrderModel? order = orderController.packagerOrderDetails;
            if (order == null) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Theme.of(context).primaryColor.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        Icons.receipt_long,
                        size: 48,
                        color: Theme.of(context).primaryColor,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No Order Details Available',
                      style: robotoMedium.copyWith(
                        fontSize: 18,
                        color: Theme.of(context).disabledColor,
                      ),
                    ),
                  ],
                ),
              );
            }
            return Column(
              children: [
                // Main Content
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: () async {
                      await Get.find<OrderController>()
                          .getPackagerOrderDetails(order.id!);
                    },
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          // Enhanced Order Header
                          Container(
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  Theme.of(context).primaryColor,
                                  Theme.of(context)
                                      .primaryColor
                                      .withOpacity(0.8),
                                ],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Theme.of(context)
                                      .primaryColor
                                      .withOpacity(0.3),
                                  blurRadius: 15,
                                  offset: const Offset(0, 8),
                                ),
                              ],
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(24),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Row(
                                              children: [
                                                Container(
                                                  padding:
                                                      const EdgeInsets.all(8),
                                                  decoration: BoxDecoration(
                                                    color: Colors.white
                                                        .withOpacity(0.2),
                                                    borderRadius:
                                                        BorderRadius.circular(
                                                            12),
                                                  ),
                                                  child: const Icon(
                                                    Icons.receipt_long,
                                                    color: Colors.white,
                                                    size: 20,
                                                  ),
                                                ),
                                                const SizedBox(width: 12),
                                                Expanded(
                                                  child: Text(
                                                    'Order #${order.id}',
                                                    style: robotoBold.copyWith(
                                                      fontSize: 22,
                                                      color: Colors.white,
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 8),
                                            Text(
                                              order.createdAt ?? '',
                                              style: robotoRegular.copyWith(
                                                fontSize: 14,
                                                color: Colors.white
                                                    .withOpacity(0.9),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 16, vertical: 8),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withOpacity(0.2),
                                          borderRadius:
                                              BorderRadius.circular(20),
                                          border: Border.all(
                                            color:
                                                Colors.white.withOpacity(0.3),
                                            width: 1,
                                          ),
                                        ),
                                        child: Text(
                                          order.orderStatus?.tr ?? '',
                                          style: robotoMedium.copyWith(
                                            fontSize: 12,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 20),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(20),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.15),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: Colors.white.withOpacity(0.2),
                                        width: 1,
                                      ),
                                    ),
                                    child: Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              'Total Amount',
                                              style: robotoRegular.copyWith(
                                                fontSize: 14,
                                                color: Colors.white
                                                    .withOpacity(0.9),
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              '₹${order.orderAmount?.toStringAsFixed(2) ?? '0.00'}',
                                              style: robotoBold.copyWith(
                                                fontSize: 28,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ],
                                        ),
                                        Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.end,
                                          children: [
                                            Text(
                                              'Payment Method',
                                              style: robotoRegular.copyWith(
                                                fontSize: 14,
                                                color: Colors.white
                                                    .withOpacity(0.9),
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              order.paymentMethod?.tr ?? '',
                                              style: robotoMedium.copyWith(
                                                fontSize: 16,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          const SizedBox(height: 20),

                          // Combined Items and Billing Section
                          if (order.itemModels != null &&
                              order.itemModels!.isNotEmpty)
                            Container(
                              width: double.infinity,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.08),
                                    blurRadius: 20,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.all(20),
                                    child: Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: Theme.of(context)
                                                .primaryColor
                                                .withOpacity(0.1),
                                            borderRadius:
                                                BorderRadius.circular(12),
                                          ),
                                          child: Icon(
                                            Icons.shopping_bag,
                                            color:
                                                Theme.of(context).primaryColor,
                                            size: 20,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Text(
                                          'Order Items',
                                          style: robotoBold.copyWith(
                                            fontSize: 18,
                                            color: Theme.of(context)
                                                .textTheme
                                                .bodyLarge!
                                                .color,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  ...order.itemModels!
                                      .asMap()
                                      .entries
                                      .map((entry) {
                                    int index = entry.key;
                                    OrderItemModel item = entry.value;
                                    bool isLast =
                                        index == order.itemModels!.length - 1;

                                    return Container(
                                      margin: const EdgeInsets.symmetric(
                                          horizontal: 20),
                                      padding: const EdgeInsets.symmetric(
                                          vertical: 16),
                                      decoration: BoxDecoration(
                                        border: !isLast
                                            ? Border(
                                                bottom: BorderSide(
                                                  color: Colors.grey
                                                      .withOpacity(0.2),
                                                  width: 1,
                                                ),
                                              )
                                            : null,
                                      ),
                                      child: Row(
                                        children: [
                                          Container(
                                            width: 50,
                                            height: 50,
                                            decoration: BoxDecoration(
                                              color: Theme.of(context)
                                                  .primaryColor
                                                  .withOpacity(0.1),
                                              borderRadius:
                                                  BorderRadius.circular(12),
                                            ),
                                            child: Icon(
                                              Icons.restaurant,
                                              color: Theme.of(context)
                                                  .primaryColor,
                                              size: 20,
                                            ),
                                          ),
                                          const SizedBox(width: 16),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  item.itemName ?? '',
                                                  style: robotoMedium.copyWith(
                                                    fontSize: 16,
                                                    color: Theme.of(context)
                                                        .textTheme
                                                        .bodyLarge!
                                                        .color,
                                                  ),
                                                ),
                                                const SizedBox(height: 4),
                                                Text(
                                                  'Qty: ${item.quantity?.toStringAsFixed(2) ?? '0'} ${item.unit ?? ''}',
                                                  style: robotoRegular.copyWith(
                                                    fontSize: 14,
                                                    color: Theme.of(context)
                                                        .hintColor,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(
                                                horizontal: 12, vertical: 6),
                                            decoration: BoxDecoration(
                                              color: Theme.of(context)
                                                  .primaryColor,
                                              borderRadius:
                                                  BorderRadius.circular(20),
                                            ),
                                            child: Text(
                                              '₹${item.total?.toStringAsFixed(2) ?? '0.00'}',
                                              style: robotoBold.copyWith(
                                                fontSize: 14,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    );
                                  }).toList(),

                                  // Billing Summary within Items Section
                                  Container(
                                    margin: const EdgeInsets.all(20),
                                    padding: const EdgeInsets.all(20),
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(
                                        colors: [
                                          Theme.of(context)
                                              .primaryColor
                                              .withOpacity(0.1),
                                          Theme.of(context)
                                              .primaryColor
                                              .withOpacity(0.05),
                                        ],
                                      ),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: Theme.of(context)
                                            .primaryColor
                                            .withOpacity(0.2),
                                        width: 1,
                                      ),
                                    ),
                                    child: Column(
                                      children: [
                                        _buildBillingRow('Subtotal',
                                            '₹${order.orderAmount?.toStringAsFixed(2) ?? '0.00'}'),
                                        if (order.couponDiscountAmount !=
                                                null &&
                                            order.couponDiscountAmount! > 0)
                                          _buildBillingRow('Discount',
                                              '-₹${order.couponDiscountAmount?.toStringAsFixed(2) ?? '0.00'}',
                                              isDiscount: true),
                                        if (order.totalTaxAmount != null &&
                                            order.totalTaxAmount! > 0)
                                          _buildBillingRow('Tax Fee',
                                              '₹${order.totalTaxAmount?.toStringAsFixed(2) ?? '0.00'}'),
                                        if (order.dmTips != null &&
                                            order.dmTips! > 0)
                                          _buildBillingRow('Delivery Tips',
                                              '₹${order.dmTips?.toStringAsFixed(2) ?? '0.00'}'),
                                        if (order.additionalCharge != null &&
                                            order.additionalCharge! > 0)
                                          _buildBillingRow('Platform Fee',
                                              '₹${order.additionalCharge?.toStringAsFixed(2) ?? '0.00'}'),
                                        if (order.deliveryCharge != null &&
                                            order.deliveryCharge! > 0)
                                          _buildBillingRow('Delivery Fee',
                                              '₹${order.deliveryCharge?.toStringAsFixed(2) ?? '0.00'}'),
                                        Container(
                                          margin: const EdgeInsets.symmetric(
                                              vertical: 16),
                                          height: 1,
                                          decoration: BoxDecoration(
                                            gradient: LinearGradient(
                                              colors: [
                                                Colors.transparent,
                                                Colors.grey.withOpacity(0.3),
                                                Colors.transparent,
                                              ],
                                            ),
                                          ),
                                        ),
                                        Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              'Total',
                                              style: robotoBold.copyWith(
                                                fontSize: 18,
                                                color: Theme.of(context)
                                                    .textTheme
                                                    .bodyLarge!
                                                    .color,
                                              ),
                                            ),
                                            Text(
                                              '₹${order.orderAmount?.toStringAsFixed(2) ?? '0.00'}',
                                              style: robotoBold.copyWith(
                                                fontSize: 20,
                                                color: Theme.of(context)
                                                    .primaryColor,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          const SizedBox(height: 20),

                          // Enhanced Order Information
                          Container(
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.08),
                                  blurRadius: 20,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Padding(
                                  padding: const EdgeInsets.all(20),
                                  child: Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Theme.of(context)
                                              .primaryColor
                                              .withOpacity(0.1),
                                          borderRadius:
                                              BorderRadius.circular(12),
                                        ),
                                        child: Icon(
                                          Icons.info_outline,
                                          color: Theme.of(context).primaryColor,
                                          size: 20,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Text(
                                        'Order Information',
                                        style: robotoBold.copyWith(
                                          fontSize: 18,
                                          color: Theme.of(context)
                                              .textTheme
                                              .bodyLarge!
                                              .color,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 20),
                                  child: Column(
                                    children: [
                                      if (order.customer != null)
                                        _buildInfoCard(
                                          Icons.person,
                                          'Customer',
                                          '${order.customer?.fName ?? 'N/A'} ${order.customer?.lName ?? ''}'
                                                  .trim()
                                                  .isEmpty
                                              ? 'Customer'
                                              : '${order.customer?.fName ?? 'N/A'} ${order.customer?.lName ?? ''}'
                                                  .trim(),
                                          '${order.customer?.email ?? 'No email'}\n${order.customer?.phone ?? 'No phone'}',
                                        ),
                                      if (order.storeModel != null)
                                        _buildInfoCard(
                                          Icons.store,
                                          'Store',
                                          order.storeModel?.name ?? '',
                                          order.storeModel?.address ?? '',
                                        ),
                                      if (order.deliveryAddress != null)
                                        _buildInfoCard(
                                          Icons.location_on,
                                          'Delivery Address',
                                          order.deliveryAddress?.address ?? '',
                                          '${order.deliveryAddress?.contactPersonName ?? ''} ${order.deliveryAddress?.contactPersonNumber ?? ''}',
                                        ),
                                      if (order.assignedDeliveryManModel !=
                                          null)
                                        _buildInfoCard(
                                          Icons.delivery_dining,
                                          'Delivery Man',
                                          order.assignedDeliveryManModel
                                                  ?.name ??
                                              '',
                                          order.assignedDeliveryManModel
                                                  ?.phone ??
                                              '',
                                        ),
                                      if (order.assignedPackagerModel != null)
                                        _buildInfoCard(
                                          Icons.inventory_2,
                                          'Packager',
                                          order.assignedPackagerModel?.name ??
                                              '',
                                          order.assignedPackagerModel?.phone ??
                                              '',
                                        ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 20),
                              ],
                            ),
                          ),

                          // Enhanced Payment Information
                          if (order.payments != null &&
                              order.payments!.isNotEmpty) ...[
                            const SizedBox(height: 20),
                            Container(
                              width: double.infinity,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.08),
                                    blurRadius: 20,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.all(20),
                                    child: Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: Theme.of(context)
                                                .primaryColor
                                                .withOpacity(0.1),
                                            borderRadius:
                                                BorderRadius.circular(12),
                                          ),
                                          child: Icon(
                                            Icons.credit_card,
                                            color:
                                                Theme.of(context).primaryColor,
                                            size: 20,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Text(
                                          'Payment Details',
                                          style: robotoBold.copyWith(
                                            fontSize: 18,
                                            color: Theme.of(context)
                                                .textTheme
                                                .bodyLarge!
                                                .color,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  ...order.payments!
                                      .asMap()
                                      .entries
                                      .map((entry) {
                                    int index = entry.key;
                                    var payment = entry.value;
                                    bool isLast =
                                        index == order.payments!.length - 1;

                                    return Container(
                                      margin: const EdgeInsets.symmetric(
                                          horizontal: 20),
                                      padding: const EdgeInsets.symmetric(
                                          vertical: 16),
                                      decoration: BoxDecoration(
                                        border: !isLast
                                            ? Border(
                                                bottom: BorderSide(
                                                  color: Colors.grey
                                                      .withOpacity(0.2),
                                                  width: 1,
                                                ),
                                              )
                                            : null,
                                      ),
                                      child: Row(
                                        children: [
                                          Container(
                                            width: 50,
                                            height: 50,
                                            decoration: BoxDecoration(
                                              color: Theme.of(context)
                                                  .primaryColor
                                                  .withOpacity(0.1),
                                              borderRadius:
                                                  BorderRadius.circular(12),
                                            ),
                                            child: Icon(
                                              Icons.payment,
                                              color: Theme.of(context)
                                                  .primaryColor,
                                              size: 20,
                                            ),
                                          ),
                                          const SizedBox(width: 16),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  '${payment.paymentMethod?.tr ?? ''} (${payment.paymentStatus?.tr ?? ''})',
                                                  style: robotoMedium.copyWith(
                                                    fontSize: 16,
                                                    color: Theme.of(context)
                                                        .textTheme
                                                        .bodyLarge!
                                                        .color,
                                                  ),
                                                ),
                                                if (payment.createdAt !=
                                                    null) ...[
                                                  const SizedBox(height: 4),
                                                  Text(
                                                    payment.createdAt!,
                                                    style:
                                                        robotoRegular.copyWith(
                                                      fontSize: 14,
                                                      color: Theme.of(context)
                                                          .hintColor,
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(
                                                horizontal: 12, vertical: 6),
                                            decoration: BoxDecoration(
                                              color: Theme.of(context)
                                                  .primaryColor,
                                              borderRadius:
                                                  BorderRadius.circular(20),
                                            ),
                                            child: Text(
                                              '₹${payment.amount?.toStringAsFixed(2) ?? '0.00'}',
                                              style: robotoBold.copyWith(
                                                fontSize: 14,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    );
                                  }).toList(),
                                  const SizedBox(height: 20),
                                ],
                              ),
                            ),
                          ],

                          // Bottom spacing for sticky buttons
                          const SizedBox(height: 100),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            );
          }),
        ),
        // Enhanced Bottom Buttons
        bottomNavigationBar: GetBuilder<OrderController>(
          builder: (orderController) {
            OrderModel? order = orderController.packagerOrderDetails;
            if (order == null) return const SizedBox.shrink();

            return Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(24),
                  topRight: Radius.circular(24),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 20,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              child: SafeArea(
                child: Row(
                  children: [
                    // Print Invoice Button
                    Expanded(
                      child: Container(
                        height: 56,
                        decoration: BoxDecoration(
                          border: Border.all(
                            color: Theme.of(context).primaryColor,
                            width: 2,
                          ),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () {
                              final authController = Get.find<AuthController>();
                              final token = authController.getUserToken();
                              if (token != null && order.id != null) {
                                Get.to(() => B2BInvoiceScreen(
                                      orderId: order.id!,
                                      token: token,
                                      orderData: order, // Pass the order data
                                    ));
                              } else {
                                showCustomSnackBar('Unable to access invoice');
                              }
                            },
                            child: Icon(
                              Icons.print,
                              size: 24,
                              color: Theme.of(context).primaryColor,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Mark as Packaged Button - Only show for packaging orders
                    if (order.orderStatus?.toLowerCase() == 'packaging')
                      Expanded(
                        flex: 2,
                        child: Container(
                          height: 56,
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                Theme.of(context).primaryColor,
                                Theme.of(context).primaryColor.withOpacity(0.8),
                              ],
                            ),
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Theme.of(context)
                                    .primaryColor
                                    .withOpacity(0.3),
                                blurRadius: 12,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: Material(
                            color: Colors.transparent,
                            child: InkWell(
                              borderRadius: BorderRadius.circular(16),
                              onTap: orderController.isLoading
                                  ? null
                                  : () async {
                                      // Check if order is in packaging status
                                      if (order.orderStatus?.toLowerCase() !=
                                          'packaging') {
                                        showCustomSnackBar(
                                          'This order cannot be marked as packaged. Only orders in packaging status can be processed.',
                                          isError: true,
                                        );
                                        return;
                                      }

                                      // Show confirmation dialog
                                      bool? confirmed = await Get.dialog<bool>(
                                        AlertDialog(
                                          title: const Text('Confirm Action'),
                                          content: const Text(
                                              'Are you sure you want to mark this order as packaged? This will transfer the order to the delivery man.'),
                                          actions: [
                                            TextButton(
                                              onPressed: () =>
                                                  Get.back(result: false),
                                              child: const Text('Cancel'),
                                            ),
                                            TextButton(
                                              onPressed: () =>
                                                  Get.back(result: true),
                                              child: const Text('Confirm'),
                                            ),
                                          ],
                                        ),
                                      );

                                      if (confirmed == true) {
                                        // Update order status to picked_up
                                        bool success = await orderController
                                            .updatePackagerOrderStatus({
                                          'order_id': order.id,
                                          'status': 'picked_up',
                                          'note':
                                              'Order marked as packaged by packager',
                                        });

                                        if (success) {
                                          // Show success message
                                          showCustomSnackBar(
                                            'Order successfully marked as packaged and transferred to delivery!',
                                            isError: false,
                                          );
                                          // Refresh order details and go back to order list
                                          await orderController
                                              .getPackagerOrderDetails(
                                                  order.id!);
                                          await orderController
                                              .getPackagingOrders();
                                          Get.back(); // Go back to order list
                                        }
                                      }
                                    },
                              child: orderController.isLoading
                                  ? const Center(
                                      child: SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          valueColor:
                                              AlwaysStoppedAnimation<Color>(
                                                  Colors.white),
                                        ),
                                      ),
                                    )
                                  : Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.check_circle,
                                          size: 20,
                                          color: Colors.white,
                                        ),
                                        SizedBox(width: 8),
                                        Column(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              'Mark as Packaged',
                                              style: TextStyle(
                                                fontSize: 16,
                                                fontWeight: FontWeight.w600,
                                                color: Colors.white,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'Transfer to Delivery',
                                              style: TextStyle(
                                                fontSize: 10,
                                                fontWeight: FontWeight.w400,
                                                color: Colors.white
                                                    .withOpacity(0.8),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildBillingRow(String label, String amount,
      {bool isDiscount = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: robotoRegular.copyWith(
              fontSize: 16,
              color: Theme.of(context).textTheme.bodyLarge!.color,
            ),
          ),
          Text(
            amount,
            style: robotoMedium.copyWith(
              fontSize: 16,
              color: isDiscount
                  ? const Color(0xFFE84D4F)
                  : Theme.of(context).textTheme.bodyLarge!.color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard(
      IconData icon, String title, String value, String subtitle) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.grey.withOpacity(0.1),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              icon,
              color: Theme.of(context).primaryColor,
              size: 20,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: robotoRegular.copyWith(
                    fontSize: 12,
                    color: Theme.of(context).hintColor,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: robotoMedium.copyWith(
                    fontSize: 16,
                    color: Theme.of(context).textTheme.bodyLarge!.color,
                  ),
                ),
                if (subtitle.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: robotoRegular.copyWith(
                      fontSize: 14,
                      color: Theme.of(context).hintColor,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
