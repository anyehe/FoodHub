import 'package:flutter/material.dart';
import '../core/services/api_client.dart';
class NotificationsScreen extends StatelessWidget {
 const NotificationsScreen({super.key});
 @override Widget build(BuildContext context)=>Scaffold(appBar:AppBar(title:const Text('Notifications')),body:FutureBuilder(future:ApiClient.get('/notifications'),builder:(context,s){if(s.connectionState!=ConnectionState.done)return const Center(child:CircularProgressIndicator());if(s.hasError)return Center(child:Text('${s.error}'));final d=s.data as Map;final rows=(d['data']??[]) as List;if(rows.isEmpty)return const Center(child:Text('No notifications'));return ListView.builder(itemCount:rows.length,itemBuilder:(c,i){final n=rows[i];final data=(n['data']??{}) as Map;return ListTile(leading:const Icon(Icons.notifications),title:Text(data['title']?.toString()??'FoodHub'),subtitle:Text(data['message']?.toString()??''),onTap:()async{await ApiClient.post('/notifications/${n['id']}/read',{});}});}));
}
