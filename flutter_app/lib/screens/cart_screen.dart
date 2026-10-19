import 'package:flutter/material.dart';
import 'checkout_screen.dart';

class CartScreen extends StatefulWidget { final List<Map<String,dynamic>> cart; const CartScreen({super.key,required this.cart}); @override State<CartScreen> createState()=>_CartState(); }
class _CartState extends State<CartScreen>{
 double get total=>widget.cart.fold(0,(a,x)=>(a as num)+((x['unit_price'] as num)*(x['quantity'] as int))).toDouble();
 @override Widget build(BuildContext c)=>Scaffold(appBar:AppBar(title:const Text('Cart')),body:Padding(padding:const EdgeInsets.all(16),child:Column(children:[Expanded(child:widget.cart.isEmpty?const Center(child:Text('Cart is empty')):ListView.builder(itemCount:widget.cart.length,itemBuilder:(c,i){final x=widget.cart[i];return Card(child:ListTile(title:Text(x['name']),subtitle:Text('${x['quantity']} × ₱${x['unit_price']}'),leading:CircleAvatar(child:Text('${x['quantity']}')),trailing:IconButton(onPressed:(){setState(()=>widget.cart.removeAt(i));},icon:const Icon(Icons.delete_outline)));})),),const Divider(),Row(mainAxisAlignment:MainAxisAlignment.spaceBetween,children:[const Text('Estimated subtotal',style:TextStyle(fontWeight:FontWeight.bold)),Text('₱${total.toStringAsFixed(2)}',style:const TextStyle(fontWeight:FontWeight.w900))]),const SizedBox(height:12),FilledButton.icon(onPressed:widget.cart.isEmpty?null:()=>Navigator.push(c,MaterialPageRoute(builder:(_)=>CheckoutScreen(cart:widget.cart))).then((_){if(mounted)setState((){});}),icon:const Icon(Icons.arrow_forward),label:const Text('Continue to checkout'))]));
}
