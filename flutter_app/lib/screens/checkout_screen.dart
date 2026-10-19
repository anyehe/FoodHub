import 'package:flutter/material.dart';
import '../core/services/api_client.dart';
import 'orders_screen.dart';

class CheckoutScreen extends StatefulWidget {
  final List<Map<String, dynamic>> cart;
  const CheckoutScreen({super.key, required this.cart});
  @override State<CheckoutScreen> createState() => _CheckoutState();
}

class _CheckoutState extends State<CheckoutScreen> {
  List<dynamic> branches = [], addresses = [];
  dynamic branch, address;
  String fulfillment = 'delivery', payment = 'cash';
  String coupon = '', notes = '';
  Map<String, dynamic>? quote;
  bool loading = true, placing = false, quoting = false;
  String? error;

  @override void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final b = await ApiClient.get('/branches');
      final a = await ApiClient.get('/addresses');
      branches = b is List ? b : (b['data'] ?? []);
      addresses = a is List ? a : (a['data'] ?? []);
      if (branches.isNotEmpty) branch = branches.first;
      if (addresses.isNotEmpty) address = addresses.first;
      await _quote();
    } catch (e) { error = e.toString(); }
    if (mounted) setState(() => loading = false);
  }

  Future<void> _quote() async {
    if (branch == null || widget.cart.isEmpty) return;
    setState(() => quoting = true);
    try {
      final r = await ApiClient.post('/checkout/quote', {
        'branch_id': branch['id'],
        'fulfillment_type': fulfillment,
        'coupon': coupon.trim().isEmpty ? null : coupon.trim(),
        'items': widget.cart.map((x) => {'product_id': x['product_id'], 'quantity': x['quantity'], 'options': x['options'] ?? []}).toList(),
      });
      quote = Map<String, dynamic>.from(r as Map);
      error = null;
    } catch (e) { error = e.toString(); }
    if (mounted) setState(() => quoting = false);
  }

  Future<void> _place() async {
    if (branch == null || widget.cart.isEmpty || (fulfillment == 'delivery' && address == null)) return;
    setState(() => placing = true);
    try {
      final r = await ApiClient.post('/orders', {
        'branch_id': branch['id'],
        'address_id': fulfillment == 'delivery' ? address['id'] : null,
        'fulfillment_type': fulfillment,
        'payment_method': payment,
        'coupon': coupon.trim().isEmpty ? null : coupon.trim(),
        'notes': notes.trim().isEmpty ? null : notes.trim(),
        'items': widget.cart.map((x) => {'product_id': x['product_id'], 'quantity': x['quantity'], 'options': x['options'] ?? [], 'notes': x['notes']}).toList(),
      });
      widget.cart.clear();
      if (!mounted) return;
      await Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => OrdersScreen(highlightOrderId: r['id'])));
    } catch (e) { setState(() => error = e.toString()); }
    if (mounted) setState(() => placing = false);
  }

  @override Widget build(BuildContext context) {
    if (loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (error != null) _error(error!),
        const Text('Order type', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17)),
        const SizedBox(height: 8),
        SegmentedButton<String>(segments: const [
          ButtonSegment(value: 'delivery', label: Text('Delivery'), icon: Icon(Icons.delivery_dining)),
          ButtonSegment(value: 'pickup', label: Text('Pickup'), icon: Icon(Icons.storefront)),
          ButtonSegment(value: 'dine_in', label: Text('Dine-in'), icon: Icon(Icons.restaurant)),
        ], selected: {fulfillment}, onSelectionChanged: (v) { setState(() => fulfillment = v.first); _quote(); }),
        const SizedBox(height: 18),
        DropdownButtonFormField<dynamic>(value: branch, decoration: const InputDecoration(labelText: 'Branch'), items: branches.map((b) => DropdownMenuItem(value: b, child: Text(b['name'] ?? 'Branch'))).toList(), onChanged: (v) { setState(() => branch = v); _quote(); }),
        if (fulfillment == 'delivery') ...[
          const SizedBox(height: 14),
          if (addresses.isEmpty) _addressNotice() else DropdownButtonFormField<dynamic>(value: address, decoration: const InputDecoration(labelText: 'Delivery address'), items: addresses.map((a) => DropdownMenuItem(value: a, child: Text('${a['label'] ?? 'Address'} — ${a['address_line'] ?? a['address'] ?? ''}', overflow: TextOverflow.ellipsis))).toList(), onChanged: (v) => setState(() => address = v)),
        ],
        const SizedBox(height: 18),
        const Text('Payment', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17)),
        RadioListTile(value: 'cash', groupValue: payment, onChanged: (v) => setState(() => payment = v!), title: const Text('Cash')),
        RadioListTile(value: 'gcash', groupValue: payment, onChanged: (v) => setState(() => payment = v!), title: const Text('GCash')),
        RadioListTile(value: 'paypal', groupValue: payment, onChanged: (v) => setState(() => payment = v!), title: const Text('PayPal')),
        RadioListTile(value: 'wallet', groupValue: payment, onChanged: (v) => setState(() => payment = v!), title: const Text('FoodHub Wallet')),
        const SizedBox(height: 8),
        TextField(decoration: const InputDecoration(labelText: 'Coupon code', prefixIcon: Icon(Icons.local_offer_outlined)), onChanged: (v) => coupon = v, onSubmitted: (_) => _quote()),
        const SizedBox(height: 10),
        TextField(decoration: const InputDecoration(labelText: 'Order notes'), maxLines: 2, onChanged: (v) => notes = v),
        const SizedBox(height: 18),
        if (quote != null) _summary(),
        FilledButton.icon(onPressed: placing || quoting ? null : _place, icon: const Icon(Icons.lock_outline), label: Text(placing ? 'Placing order…' : 'Place order securely')),
      ]),
    );
  }

  Widget _summary() => Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(children: [
    _row('Subtotal', quote!['subtotal']), _row('Discount', quote!['discount_total']), _row('Tax', quote!['tax_total']), _row('Delivery', quote!['delivery_fee']), const Divider(), _row('Total', quote!['grand_total'], bold: true),
  ])));
  Widget _row(String label, dynamic value, {bool bold = false}) => Padding(padding: const EdgeInsets.symmetric(vertical: 4), child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(label, style: TextStyle(fontWeight: bold ? FontWeight.w800 : FontWeight.normal)), Text('₱${value ?? '0.00'}', style: TextStyle(fontWeight: bold ? FontWeight.w900 : FontWeight.normal))]));
  Widget _error(String e) => Container(margin: const EdgeInsets.only(bottom: 12), padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)), child: Text(e, style: TextStyle(color: Colors.red.shade800)));
  Widget _addressNotice() => Card(child: const ListTile(leading: Icon(Icons.location_off_outlined), title: Text('Add a delivery address'), subtitle: Text('Your account needs a saved address before delivery checkout.')));
}
