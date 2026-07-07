import 'package:flutter/material.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_details_model.dart';

class EditQuantityDialog extends StatefulWidget {
  final OrderDetailsModel item;
  final Function(double, String) onSubmit;

  const EditQuantityDialog({
    required this.item,
    required this.onSubmit,
    Key? key,
  }) : super(key: key);

  @override
  State<EditQuantityDialog> createState() => _EditQuantityDialogState();
}

class _EditQuantityDialogState extends State<EditQuantityDialog> {
  late double _quantity;
  String _reason = '';

  @override
  void initState() {
    super.initState();
    _quantity = widget.item.quantity!;
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text('Edit Quantity'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('Current: ${widget.item.quantity!.toStringAsFixed(1)}'),
          TextField(
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: InputDecoration(labelText: 'New Quantity'),
            onChanged: (val) =>
                _quantity = (double.tryParse(val) ?? widget.item.quantity)!,
          ),
          TextField(
            decoration: InputDecoration(labelText: 'Reason'),
            onChanged: (val) => _reason = val,
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            widget.onSubmit(_quantity, _reason);
            Navigator.pop(context);
          },
          child: Text('Submit'),
        ),
      ],
    );
  }
}
