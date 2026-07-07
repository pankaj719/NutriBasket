import 'package:flutter/material.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_details_model.dart';

class EditAllQuantitiesDialog extends StatefulWidget {
  final String orderId;
  final List<OrderDetailsModel> items;

  const EditAllQuantitiesDialog({
    Key? key,
    required this.orderId,
    required this.items,
  }) : super(key: key);

  @override
  State<EditAllQuantitiesDialog> createState() =>
      _EditAllQuantitiesDialogState();
}

class _EditAllQuantitiesDialogState extends State<EditAllQuantitiesDialog> {
  late List<TextEditingController> _controllers;

  @override
  void initState() {
    super.initState();
    _controllers = widget.items
        .map((item) =>
            TextEditingController(text: item.quantity!.toStringAsFixed(1)))
        .toList();
  }

  @override
  void dispose() {
    for (final c in _controllers) {
      c.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text('Edit All Quantities'),
      content: SizedBox(
        width: double.maxFinite,
        child: ListView.builder(
          shrinkWrap: true,
          itemCount: widget.items.length,
          itemBuilder: (context, index) {
            final item = widget.items[index];
            return Row(
              children: [
                Expanded(
                    child:
                        Text(item.itemDetails?.name ?? 'Item ${item.itemId}')),
                SizedBox(
                  width: 60,
                  child: TextField(
                    controller: _controllers[index],
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                    decoration: InputDecoration(labelText: 'Qty'),
                  ),
                ),
              ],
            );
          },
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            final changes = <Map<String, dynamic>>[];
            for (int i = 0; i < widget.items.length; i++) {
              final newQty = double.tryParse(_controllers[i].text) ??
                  widget.items[i].quantity!;
              if (newQty != widget.items[i].quantity) {
                changes.add({
                  'item_id': widget.items[i].id.toString(),
                  'new_quantity': newQty,
                  'reason': '', // Optionally add a reason field
                });
              }
            }
            Navigator.pop(context, changes);
          },
          child: Text('Submit'),
        ),
      ],
    );
  }
}
