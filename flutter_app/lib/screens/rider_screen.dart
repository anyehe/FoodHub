import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../core/services/api_client.dart';
import '../core/services/session.dart';
class RiderScreen extends StatefulWidget { const RiderScreen({super.key}); @override State<RiderScreen> createState()=>_RiderState(); }
class _RiderState extends State<RiderScreen>{List<dynamic> rows=[];bool online=false;bool loading=true;String? error;Timer? locationTimer;
 Future<void> load()async{try{final r=await ApiClient.get('/rider/queue');if(mounted)setState(()=>rows=r as List);}catch(e){if(mounted)setState(()=>error='$e');}finally{if(mounted)setState(()=>loading=false);}}
 Future<void> toggle(bool v)async{try{await ApiClient.post('/rider/availability',{'is_online':v});setState(()=>online=v);}catch(e){setState(()=>error='$e');}}
 Future<void> action(int id,String action)async{try{await ApiClient.post('/rider/assignments/$id/$action',{});await load();}catch(e){setState(()=>error='$e');}}
 Future<void> sendLocation(int id)async{try{var p=await Geolocator.getCurrentPosition(locationSettings:const LocationSettings(accuracy:LocationAccuracy.high));await ApiClient.post('/rider/assignments/$id/location',{'latitude':p.latitude,'longitude':p.longitude});}catch(e){setState(()=>error='$e');}}
 @override void initState(){super.initState();load(); locationTimer=Timer.periodic(const Duration(seconds:15),(_){_pushActiveLocations();});}
 @override void dispose(){locationTimer?.cancel();super.dispose();}
 Future<void> _pushActiveLocations()async{for(final a in rows){final status=a['status'];if(status=='accepted'||status=='picked_up'){await sendLocation(a['id']);}}}
 @override Widget build(BuildContext c)=>Scaffold(appBar:AppBar(title:const Text('Rider Operations'),actions:[Switch(value:online,onChanged:toggle),IconButton(onPressed:()async{await Session.clear();if(c.mounted)Navigator.pop(c);},icon:const Icon(Icons.logout))]),body:RefreshIndicator(onRefresh:load,child:loading?const Center(child:CircularProgressIndicator()):ListView(padding:const EdgeInsets.all(16),children:[if(error!=null)Text(error!,style:const TextStyle(color:Colors.red)),...rows.map((a){final o=a['order']??{};final status=a['status']??'';return Card(child:Padding(padding:const EdgeInsets.all(14),child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:[Text(o['order_number']??'',style:const TextStyle(fontWeight:FontWeight.bold)),Text(o['branch']?['name']??'Branch'),Text('Status: $status'),const SizedBox(height:8),Wrap(spacing:8,children:[if(status=='offered')FilledButton(onPressed:()=>action(a['id'],'accept'),child:const Text('Accept')),if(status=='accepted')FilledButton(onPressed:()=>action(a['id'],'pickup'),child:const Text('Pick up')),if(status=='picked_up')FilledButton(onPressed:()=>action(a['id'],'out-for-delivery'),child:const Text('Start delivery')),if(status=='picked_up'||status=='accepted')OutlinedButton(onPressed:()=>sendLocation(a['id']),child:const Text('Send location')),if(status=='picked_up')FilledButton(onPressed:()=>action(a['id'],'deliver'),child:const Text('Delivered'))])]))})]));
}
