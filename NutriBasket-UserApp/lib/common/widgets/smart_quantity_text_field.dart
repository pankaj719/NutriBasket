import 'package:flutter/material.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';

class SmartQuantityTextField extends StatefulWidget {
  final double? initialValue;
  final Function(double) onChanged;
  final double? width;
  final bool showBorder;
  final bool showTitle;

  const SmartQuantityTextField({
    super.key,
    this.initialValue,
    required this.onChanged,
    this.width = 80,
    this.showBorder = true,
    this.showTitle = false,
  });

  @override
  State<SmartQuantityTextField> createState() => _SmartQuantityTextFieldState();
}

class _SmartQuantityTextFieldState extends State<SmartQuantityTextField> {
  late TextEditingController _controller;
  late FocusNode _focusNode;
  bool _hasFocus = false;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: widget.initialValue?.toString() ?? '');
    _focusNode = FocusNode();
    
    _focusNode.addListener(() {
      setState(() {
        _hasFocus = _focusNode.hasFocus;
      });
      
      // When losing focus, ensure we have a valid value
      if (!_hasFocus && _controller.text.isNotEmpty) {
        final parsed = double.tryParse(_controller.text);
        if (parsed == null || parsed <= 0) {
          _controller.text = widget.initialValue?.toString() ?? '1';
          widget.onChanged(widget.initialValue ?? 1);
        }
      }
    });
  }

  @override
  void didUpdateWidget(SmartQuantityTextField oldWidget) {
    super.didUpdateWidget(oldWidget);
    
    // Only update the controller text if the field is not focused
    // This prevents interfering with user input
    if (!_hasFocus) {
      final newValue = widget.initialValue?.toString() ?? '';
      if (_controller.text != newValue) {
        _controller.text = newValue;
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: widget.width,
      child: CustomTextField(
        controller: _controller,
        focusNode: _focusNode,
        inputType: const TextInputType.numberWithOptions(decimal: true),
        isAmount: true,
        showBorder: widget.showBorder,
        showTitle: widget.showTitle,
        onChanged: (value) {
          if (value.isNotEmpty) {
            final parsed = double.tryParse(value);
            if (parsed != null && parsed > 0) {
              widget.onChanged(parsed);
            }
          }
        },
      ),
    );
  }
}
