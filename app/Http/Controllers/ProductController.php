<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGallery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $productTypes = array('ikan','kepiting','cumi','kerang');
    protected $owner_id = null;

    public function __construct()
    {
        $this->middleware('auth:admin');
       
    }

    public function index()
    {
         // Get current Admin Id 
        $owner_id = Auth::guard('admin')->user()->id;

        $products = Product::with(['owner','product_galleries'])->where('owned_by', $owner_id)->get();
        return view('pages.admin.products.index', compact('products'));
    }

    //Product create view
    public function create()
    {
        $productTypes = $this->productTypes;
        return view('pages.admin.products.create', compact('productTypes'));
    }

    /* Store a newly created resource in storage. */
    public function store(Request $request)
    {

        $validated = $this->validate($request, [
            'name' => ['required','max:100'],
            'type' => ['required','max:50'],
            'weight' => ['required','max:100','min:1'],
            'description' => ['required'],
            'price' => ['required','max:255'],
            'grosir_price' => ['required','max:255'],
            'grosir_min' => ['required','max:255'],
            'stock' => ['required'],
            'photo' =>  ['required', 'image']
        ]);

        $owner_id = Auth::guard('admin')->user()->id;

        $validated['slug'] = Str::slug($request->name);
        $validated['owned_by'] = $owner_id;

        // Store Product
        $newProduct = Product::create($validated);
        
        // Store Product Image
        $productImage['product_id'] = $newProduct->id;
        $productImage['image'] = $request->file('photo')->store('assets/products', 'public');
        $productImage['is_default'] = 1;

        ProductGallery::create($productImage);
        return redirect('/admin/products');

    }

    /* Display the specified resource. */
    public function show($id)
    {   
        $products = Product::with(['product_galleries'])->findOrFail($id);
        // dd(gettype($products));
        return view('pages.admin.products.detail', compact('products'));
    }

    /** Show the form for editing the specified resource. */
    public function edit($id)
    {
        $product = Product::findOrFail(Crypt::decrypt($id));
        $productTypes = $this->productTypes;
        return view('pages.admin.products.edit', compact(['product','productTypes']));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request, $id)
    {
        // Validating new Product
        $newProduct = $this->validate($request, [
            'name' => ['required','max:100'],
            'type' => ['required','max:50'],
            'weight' => ['required','max:100','min:1'],
            'description' => ['required'],
            'price' => ['required','max:255'],
            'grosir_price' => ['required','max:255'],
            'grosir_min' => ['required','max:255'],
            'stock' => ['required'],
            'photo' =>  ['required', 'image']
        ]);

        $newProduct['slug'] = Str::slug($request->name);

        $owner_id = Auth::guard('admin')->user()->id;
        $newProduct['owned_by'] = $owner_id;

        // Update Product
        $updatedProduct = Product::findOrFail($id);
        $updatedProduct->update($newProduct);  

        // Update Image Product
        $newProductImage['product_id'] = $id;
        $newProductImage['image'] = $request->file('photo')->store('assets/products', 'public');
        $newProductImage['is_default'] = 1;

        $newImage = ProductGallery::where('product_id', $id);
        $newImage->update($newProductImage);
        
        return redirect('/admin/products');
    }

    /** Remove the specified resource from storage. */
    public function destroy($id)
    {
        Product::destroy($id);
        ProductGallery::where('product_id', $id)->delete();
        return redirect()->back();
    }
}
