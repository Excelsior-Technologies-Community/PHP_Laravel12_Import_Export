<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ProductsImport implements ToCollection, WithHeadingRow
{

    public $totalRows = 0;
    public $successRows = 0;
    public $failedRows = 0;
    public $skippedRows = 0;



    public function collection(Collection $rows)
    {

        foreach ($rows as $row)
        {

            $this->totalRows++;


            try
            {

                $sku = trim((string) $row['sku']);


                if($sku == '')
                {
                    $this->failedRows++;
                    continue;
                }



                // Normalize SKU check
                $existingProduct = Product::whereRaw(
                    'LOWER(TRIM(sku)) = ?',
                    [
                        strtolower($sku)
                    ]
                )->first();



                if($existingProduct)
                {

                    // update existing product

                    $existingProduct->update([

                        'name' => $row['name'] ?? $existingProduct->name,

                        'description' => $row['description'] ?? $existingProduct->description,

                        'price' => $row['price'] ?? $existingProduct->price,

                        'quantity' => $row['quantity'] ?? $existingProduct->quantity,

                        'category' => $row['category'] ?? $existingProduct->category,

                    ]);


                    $this->skippedRows++;

                    continue;

                }




                $product = new Product();


                $product->name = $row['name'] ?? 'Unknown Product';

                $product->description = $row['description'] ?? null;

                $product->price = (float)($row['price'] ?? 0);

                $product->quantity = (int)($row['quantity'] ?? 0);

                $product->category = $row['category'] ?? 'Uncategorized';

                $product->sku = $sku;


                $product->save();



                if(!empty($row['images']))
                {
                    $this->processImages(
                        $product,
                        $row['images']
                    );
                }



                $this->successRows++;


            }
            catch(\Throwable $e)
            {

                logger(
                    "IMPORT ERROR: ".$e->getMessage()
                );

                $this->failedRows++;

            }


        }

    }




    private function processImages($product,$imagesData)
    {

        $images = explode(',', $imagesData);


        $order = 0;


        foreach($images as $image)
        {

            $image = trim($image);


            if($image)
            {

                ProductImage::create([

                    'product_id'=>$product->id,

                    'image_path'=>$image,

                    'order'=>$order++

                ]);

            }

        }

    }

}