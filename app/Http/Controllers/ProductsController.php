<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    // Aturan validasi untuk Create
    private $rules = [
        'name'        => 'required|string|min:5|max:100',
        'sku'         => 'required|string|max:50|unique:products,sku',
        'category'    => 'required|string|max:50',
        'description' => 'required|string|min:10',
        'price'       => 'required|numeric|min:0',
        'stock'       => 'required|integer|min:0',
        'status'      => 'required|in:active,inactive',
    ];

    // ---------- Helper ----------

    private function getFormat(Request $request)
    {
        $accept = strtolower(trim((string) $request->header('Accept')));
        if (strpos($accept, 'application/json') !== false) return 'json';
        if (strpos($accept, 'application/xml') !== false) return 'xml';
        return null;
    }

    private function isJsonContent(Request $request)
    {
        return strpos((string) $request->header('Content-Type'), 'application/json') === 0;
    }

    private function toXml($data, \SimpleXMLElement $xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->toXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    private function respond($data, $status, $format, $root = 'response')
    {
        if ($format === 'xml') {
            $xml = new \SimpleXMLElement("<$root/>");
            $this->toXml($data, $xml);
            return response($xml->asXML(), $status)
                ->header('Content-Type', 'application/xml');
        }
        return response()->json($data, $status);
    }

    private function notAcceptable()
    {
        return response('Not Acceptable!', 406);
    }

    private function unsupportedMedia()
    {
        return response('Unsupported Media Type', 415);
    }

    // ---------- CRUD ----------

    // GET /products  (Accept) -> response sudah dirapikan
    public function index(Request $request)
    {
        $format = $this->getFormat($request);
        if (!$format) return $this->notAcceptable();

        $products = Product::orderBy('id', 'DESC')->paginate(2)->toArray();

        $response = [
            'total_count' => $products['total'],
            'limit'       => $products['per_page'],
            'pagination'  => [
                'next_page'    => $products['next_page_url'],
                'current_page' => $products['current_page'],
            ],
            'data'        => $products['data'],
        ];

        return $this->respond($response, 200, $format, 'products');
    }

    // POST /products  (Accept + Content-Type + validasi)
    public function store(Request $request)
    {
        $format = $this->getFormat($request);
        if (!$format) return $this->notAcceptable();
        if (!$this->isJsonContent($request)) return $this->unsupportedMedia();

        $input = $request->all();

        $validator = Validator::make($input, $this->rules);
        if ($validator->fails()) {
            return $this->respond($validator->errors()->toArray(), 400, $format, 'errors');
        }

        $product = Product::create($input);
        return $this->respond($product->toArray(), 201, $format, 'product');
    }

    // GET /products/{id}  (Accept)
    public function show(Request $request, $id)
    {
        $format = $this->getFormat($request);
        if (!$format) return $this->notAcceptable();

        $product = Product::find($id);
        if (!$product) {
            return $this->respond(['message' => 'Product not found'], 404, $format);
        }

        return $this->respond($product->toArray(), 200, $format, 'product');
    }

    // PUT /products/{id}  (Accept + Content-Type + validasi)
    public function update(Request $request, $id)
    {
        $format = $this->getFormat($request);
        if (!$format) return $this->notAcceptable();
        if (!$this->isJsonContent($request)) return $this->unsupportedMedia();

        $product = Product::find($id);
        if (!$product) {
            return $this->respond(['message' => 'Product not found'], 404, $format);
        }

        $input = $request->all();

        $rules = [
            'name'        => 'sometimes|required|string|min:5|max:100',
            'sku'         => 'sometimes|required|string|max:50|unique:products,sku,' . $id,
            'category'    => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string|min:10',
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'status'      => 'sometimes|required|in:active,inactive',
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->respond($validator->errors()->toArray(), 400, $format, 'errors');
        }

        $product->fill($input);
        $product->save();

        return $this->respond($product->toArray(), 200, $format, 'product');
    }

    // DELETE /products/{id}  (Accept)
    public function destroy(Request $request, $id)
    {
        $format = $this->getFormat($request);
        if (!$format) return $this->notAcceptable();

        $product = Product::find($id);
        if (!$product) {
            return $this->respond(['message' => 'Product not found'], 404, $format);
        }

        $product->delete();

        return $this->respond([
            'message'    => 'deleted successfully',
            'product_id' => $id
        ], 200, $format);
    }
}