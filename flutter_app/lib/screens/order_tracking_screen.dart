import 'dart:async';
import 'package:flutter/material.dart';
import '../core/services/api_client.dart';

class OrderTrackingScreen extends StatefulWidget {
  final int orderId;
  const OrderTrackingScreen({super.key, required this.orderId});
  @override State<OrderTrackingScreen> createState() => _TrackingState();
}

class _TrackingState extends State<OrderTrackingScreen> {
  Map<String, dynamic>? data;
  Timer? timer;
  String? error;
  bool loading = true;
  @override void initState() { super.initState(); _load(); timer = Timer.periodic(const Duration(seconds: 10), (_) => _load(silent: true)); }
  @override void dispose() { timer?.cancel(); super.dispose(); }
  Future<void> _load({bool silent = false}) async {
    try { final r = await ApiClient.get('/orders/${widget.orderId}/tracking'); if (mounted) setState(() { data = Map<String, dynamic>.from(r); error = null; loading = false; }); }
    catch (e) { if (mounted) setState(() { error = e.toString(); loading = false; }); }
  }
  @override Widget build(BuildContext context) {
    final order = data?['order'] as Map?;
    final history = (order?['status_history'] ?? order?['statusHistory'] ?? data?['statusHistory'] ?? []) as List;
    final assignment = data?['assignment'] as Map?;
    final loc = data?['rider_location'] as Map?;
    return Scaffold(appBar: AppBar(title: Text(order?['order_number'] ?? 'Track order'), actions: [IconButton(onPressed: _load, icon: const Icon(Icons.refresh))]), body: loading ? const Center(child: CircularProgressIndicator()) : error != null ? Center(child: Text(error!)) : RefreshIndicator(onRefresh: _load, child: ListView(padding: const EdgeInsets.all(16), children: [
      _hero(order),
      const SizedBox(height: 14),
      if (assignment != null && loc?['latitude'] != null) _riderCard(assignment, loc!),
      const SizedBox(height: 14),
      const Text('Order progress', style: TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
      const SizedBox(height: 8),
      _timeline(order?['status']?.toString() ?? 'pending', history),
    ])));
  }
  Widget _hero(Map? o) => Card(child: Padding(padding: const EdgeInsets.all(18), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(o?['branch']?['name'] ?? 'FoodHub', style: const TextStyle(fontWeight: FontWeight.w800)), const SizedBox(height: 6), Text((o?['status'] ?? 'pending').toString().replaceAll('_', ' ').toUpperCase(), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)), const SizedBox(height: 8), Text('Total ₱${o?['grand_total'] ?? '0.00'}'), if (o?['address']?['address_line'] != null) Text('Deliver to: ${o?['address']['address_line']}')])));
  Widget _riderCard(Map assignment, Map loc) => Card(color: Theme.of(context).colorScheme.surfaceContainerHighest, child: ListTile(leading: const CircleAvatar(child: Icon(Icons.delivery_dining)), title: Text(assignment['rider']?['name'] ?? 'Your rider'), subtitle: Text('Live location: ${double.parse('${loc['latitude']}').toStringAsFixed(5)}, ${double.parse('${loc['longitude']}').toStringAsFixed(5)}'), trailing: const Icon(Icons.my_location)));
  Widget _timeline(String current, List history) { final states = ['pending','confirmed','preparing','ready','assigned','picked_up','out_for_delivery','delivered','completed']; final idx = states.indexOf(current); return Column(children: states.map((s) { final done = states.indexOf(s) <= idx && idx >= 0; final found = history.any((h) => h is Map && h['status'] == s); return ListTile(contentPadding: EdgeInsets.zero, leading: CircleAvatar(radius: 14, backgroundColor: done ? Theme.of(context).colorScheme.primary : Theme.of(context).colorScheme.surfaceContainerHighest, child: Icon(done ? Icons.check : Icons.circle, size: 14, color: done ? Colors.white : Colors.grey)), title: Text(s.replaceAll('_', ' ').toUpperCase(), style: TextStyle(fontWeight: done || found ? FontWeight.w800 : FontWeight.normal)),); }).toList()); }
}
