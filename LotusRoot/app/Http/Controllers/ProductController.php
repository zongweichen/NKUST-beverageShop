<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
//Model
use App\Models\Product;


class ProductController extends Controller
{
    public function indexForMainPage()
    {
        $products = Product::find(13);
        return view('index', compact('products'));
    }
    public function index()
    {
        $products = Product::paginate(5);
        return view('product.onlineShop', compact('products'));
    }

    public function create()
    {
        $products = Product::all(); //取得所有輸入資料
        // dd($products);      
        return view('manage.revise_product',compact("products"));
        // print_r($products);
    }

    public function description($id)
    {
        $product = Product::find($id); //取得所有輸入資料
        // dd($product);      
        return view('product.product_description',compact("product"));
        // print_r($product);
    }

    public function onlineShop()
    {
        return view('product.onlineShop');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|max:255',
            'description' => 'required|max:255',
            'price' => 'required|numeric|min:1',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|numeric|min:1',
            "has_sugar" => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        // 上傳圖片處理
        $path = null;
        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('products', 'public');
        }

        // 儲存到資料庫
        Product::create([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'image_url' => $path,
            'category_id' => $request->category_id,
            'has_sugar' => $request->has_sugar,
        ]);
    
        return redirect("/manage/product/create")->with('success', '產品新增成功！');
    }

    public function update(Request $request, $id)
    {
        //先取得對應的資料庫欄位資料
        $product = Product::find($id);
        // dd($product);
        //驗證輸入資料
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|max:255',
            'description' => 'required|max:255',
            'price' => 'required|numeric|min:1',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|numeric|min:1',
            "has_sugar" => 'required|boolean',
        ]);

        //如果驗證失敗，返回錯誤訊息
        if ($validator->fails()) {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        // 上傳圖片處理
        if ($request->hasFile("image_url")) {
            //刪除舊相片
            $filePath = storage_path('app/public/' . $product->image_url);
            if (file_exists($filePath)) {
                unlink($filePath); // 刪除檔案
            } 
            //上傳新相片
            $path = $request->file("image_url")->store("products", "public");
            $product->image_url = $path;
        }

        // 更新資料庫
        $product->product_name = $request->product_name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->has_sugar = $request->has_sugar;
        $product->save();

        
        return redirect("/manage/product/create")->with('success', '產品更新成功！');
    }

    public function destroy($id)
    {   //先取得對應的資料庫欄位資料
        $product = Product::find($id);

        //刪除相片
        if (!$product->image_url == null) {
            $filePath = storage_path('app/public/' . $product->image_url);
            if (file_exists($filePath)) {
                unlink($filePath); // 刪除檔案
                $product->delete();
                return redirect("/manage/product/create")->with('success', '產品刪除成功！');
            } else {
                $product->delete();
                return redirect("/manage/product/create")->with('success', '產品刪除成功！');
            }
        } else {
            $product->delete();
            return redirect("/manage/product/create")->with('success', '產品刪除成功！');
        }
    }
}
