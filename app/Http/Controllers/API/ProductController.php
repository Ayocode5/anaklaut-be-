<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function all(Request $request)
    {

        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $slug = $request->input('slug');
        $type = $request->input('type');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        try {
            //code...
            if ($id) {
                $products = Product::with(["product_galleries","owner"])->find($id);

                if ($products) {
                    return ResponseFormatter::success($products,200);
                } else {
                    return ResponseFormatter::error($message = "Produk Tidak Ada");
                }
            } else if ($slug) {
                $products = Product::with(["product_galleries","owner"])->where("slug", $slug)->first();

                if ($products) {
                    return ResponseFormatter::success($products);
                } else {
                    return ResponseFormatter::error($message = "Produk Tidak Ada");
                }
            }

            $products = Product::with(["product_galleries","owner"]);
            if ($name) {
                $products->where("name", "like", "%" . $name . "%")->get();
            }
            if ($type) {
                $products->where("type", "like", "%" . $type . "%");
            }
            if ($price_from)
                $products->where('price', '>=', $price_from);
            if ($price_to)
                $products->where('price', '<=', $price_to);

            return ResponseFormatter::success($products->paginate($limit));

        } catch (\Throwable $th) {
            Log::error($th);
            return ResponseFormatter::error($data=$th, $message="INTERNAL SERVE ERROR", $code=500);
        }
    }
}
