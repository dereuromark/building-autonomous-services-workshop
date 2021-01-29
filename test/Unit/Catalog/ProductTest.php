<?php
declare(strict_types=1);

namespace Catalog;

use Generator;
use Test\Integration\EntityTest;

final class ProductTest extends EntityTest
{
    /**
     * @test
     */
    public function it_has_an_id_and_a_name(): void
    {
        $productId = ProductId::create();
        $name = 'Name';

        $product = new Product($productId, $name);

        self::assertEquals((string)$productId, $product->id());
        self::assertEquals($name, $product->name());
    }

    protected function getObject(): Generator
    {
        yield new Product(ProductId::create(), 'Name');
    }
}
