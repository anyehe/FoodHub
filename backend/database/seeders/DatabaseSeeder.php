<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User,Branch,Category,Product,ProductOption,Coupon,Address,InventoryItem,RestaurantTable,Wallet,LoyaltyAccount,Promotion};
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  $branch=Branch::create(['name'=>'FoodHub Central','code'=>'FH-CENTRAL','phone'=>'09170000000','email'=>'central@foodhub.test','address'=>'Quezon City, Metro Manila','latitude'=>14.6760,'longitude'=>121.0437,'delivery_radius_km'=>12]);
  $users=[]; foreach([
   ['name'=>'FoodHub Admin','email'=>'admin@foodhub.test','role'=>'admin'],
   ['name'=>'Demo Customer','email'=>'customer@foodhub.test','role'=>'customer'],
   ['name'=>'Demo Rider','email'=>'rider@foodhub.test','role'=>'rider'],
   ['name'=>'Kitchen Lead','email'=>'kitchen@foodhub.test','role'=>'kitchen'],
   ['name'=>'Branch Manager','email'=>'manager@foodhub.test','role'=>'branch_manager'],
   ['name'=>'Demo Waiter','email'=>'waiter@foodhub.test','role'=>'waiter'],
  ] as $u){$users[$u['role']]=User::create($u+['password'=>Hash::make('password'),'branch_id'=>$u['role']==='customer'?null:$branch->id,'phone'=>'09000000000']);}
  Address::create(['user_id'=>$users['customer']->id,'label'=>'Home','recipient_name'=>'Demo Customer','phone'=>'09000000000','line1'=>'123 Demo Street','city'=>'Quezon City','province'=>'Metro Manila','postal_code'=>'1100','latitude'=>14.6760,'longitude'=>121.0437,'is_default'=>true]);
  $cats=[]; foreach(['Burgers','Rice Meals','Pizza','Drinks','Desserts'] as $i=>$n) $cats[$n]=Category::create(['name'=>$n,'slug'=>Str::slug($n),'description'=>'FoodHub '.$n,'sort_order'=>$i]);
  $products=[['Classic FoodHub Burger','Burgers',199],['Crispy Chicken Rice','Rice Meals',179],['Four Cheese Pizza','Pizza',349],['Iced Lemon Tea','Drinks',79],['Chocolate Lava Cake','Desserts',129]];
  foreach($products as [$name,$cat,$price]){$p=Product::create(['category_id'=>$cats[$cat]->id,'name'=>$name,'slug'=>Str::slug($name),'description'=>'Freshly prepared by FoodHub.','base_price'=>$price,'tax_rate'=>.12,'is_featured'=>true,'preparation_minutes'=>15]);$p->branches()->attach($branch->id,['price'=>$price,'is_available'=>true]);ProductOption::create(['product_id'=>$p->id,'name'=>'Extra cheese','price'=>35]);ProductOption::create(['product_id'=>$p->id,'name'=>'No onions','price'=>0]);}
  Wallet::create(['user_id'=>$users['customer']->id,'balance'=>500,'currency'=>'PHP']); LoyaltyAccount::create(['user_id'=>$users['customer']->id,'points'=>250,'tier'=>'bronze']);
  Promotion::create(['branch_id'=>$branch->id,'title'=>'FoodHub Welcome Week','slug'=>'foodhub-welcome-week','description'=>'Save with selected FoodHub offers.','coupon_code'=>'WELCOME50','discount_type'=>'fixed','discount_value'=>50,'minimum_order'=>250,'maximum_discount'=>50,'starts_at'=>now()->subDay(),'ends_at'=>now()->addMonths(3),'usage_limit'=>1000,'is_active'=>true,'priority'=>100]);
  Coupon::create(['code'=>'WELCOME50','type'=>'fixed','value'=>50,'minimum_order'=>250,'maximum_discount'=>50,'starts_at'=>now()->subDay(),'ends_at'=>now()->addMonths(3),'usage_limit'=>100,'is_active'=>true]);
  foreach([['name'=>'Burger Buns','sku'=>'INV-BUN','unit'=>'pcs','quantity'=>100,'reorder_level'=>20,'cost'=>8],['name'=>'Chicken Fillet','sku'=>'INV-CHK','unit'=>'kg','quantity'=>35,'reorder_level'=>10,'cost'=>220],['name'=>'Cheese','sku'=>'INV-CHS','unit'=>'kg','quantity'=>15,'reorder_level'=>5,'cost'=>350]] as $i) InventoryItem::create($i+['branch_id'=>$branch->id]);
  foreach([['name'=>'Table 1','code'=>'T01','capacity'=>2],['name'=>'Table 2','code'=>'T02','capacity'=>4],['name'=>'Table 3','code'=>'T03','capacity'=>6]] as $t) RestaurantTable::create($t+['branch_id'=>$branch->id,'qr_token'=>Str::uuid()]);
 }
}
