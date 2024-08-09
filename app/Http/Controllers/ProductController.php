<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // --Esse metodo irá-- mostrar a página de produto
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->get();
        return view('products.list',[
            'products' => $products
        ]);
    }

    // -- -- mostrar criar página de produto
    public function create()
    {
        return view('products.create');
    }

    // -- -- armazenar um produto no BD
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if ($request->image != "") {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        // Aqui irá inserir produto no BD
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != "") {
            // Aqui nós criaremos a image
            $image = $request->image;
            $ext =$image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; // Único nome pra image

            // Salvar a image no diretorio de products
            $image->move(public_path('uploads/products'),$imageName);

            // Salvar o nome da image no BD
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    // -- -- mostrar editar página do produto
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', [
            'product' => $product
        ]);

    }

    // -- -- atualizar o produto
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);
        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if ($request->image != "") {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withInput()->withErrors($validator);
        }

        // Aqui irá atualizar produto
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != "") {
            // Deletar image antiga
            File::delete(public_path('uploads/products/'. $product->image));

            // Aqui nós criaremos a image
            $image = $request->image;
            $ext =$image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; // Único nome pra image

            // Salvar a image no diretorio de products
            $image->move(public_path('uploads/products'),$imageName);

            // Salvar o nome da image no BD
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // -- -- apagar um produto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Deletar image antiga
        File::delete(public_path('uploads/products/'. $product->image));

        // Deletar um produto do BD
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
