import 'package:flutter/material.dart';
import '../core/services/api_client.dart';
import 'order_tracking_screen.dart';

class OrdersScreen extends StatefulWidget {
  final dynamic highlightOrderId;
  const OrdersScreen({super.key, this.highlightOrderId});
  @override State<OrdersScreen> createState() => _OrdersState();
}
class _OrdersState extends State<OrdersScreen> {
  List<dynamic> rows = []; bool loading = true; String? error;
  Future<void> load() async { try { final d = await ApiClient.get('/orders'); rows = d is Map ? (d['data'] ?? []) : d; error = null; } catch(e) { error = e.toString(); } if(mounted) setState(() => loading = false); }
  @override void initState(){super.initState();load();}
  @override Widget build(BuildContext c) => Scaffold(appBar: AppBar(title: const Text('My Orders')), body: loading ? const Center(child: CircularProgressIndicator()) : error != null ? Center(child: Text(error!)) : RefreshIndicator(onRefresh: load, child: rows.isEmpty ? ListView(children: const [SizedBox(height: 180), Center(child: Text('No orders yet'))]) : ListView.builder(padding: const EdgeInsets.all(10), itemCount: rows.length, itemBuilder: (c,i){final o=rows[i]; final active=!['completed','cancelled'].contains(o['status']); return Card(margin: const EdgeInsets.only(bottom:10), child: ListTile(contentPadding: const EdgeInsets.all(12), leading: CircleAvatar(child: Icon(active ? Icons.delivery_dining : Icons.receipt_long)), title: Text(o['order_number'] ?? '', style: const TextStyle(fontWeight: FontWeight.w800)), subtitle: Text('${(o['status'] ?? '').toString().replaceAll('_',' ').toUpperCase()} • ₱${o['grand_total']}'), trailing: const Icon(Icons.chevron_right), onTap: ()=>Navigator.push(c, MaterialPageRoute(builder: (_)=>OrderTrackingScreen(orderId: o['id']))));}))); 
}
