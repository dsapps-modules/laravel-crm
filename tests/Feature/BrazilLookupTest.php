<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Services\BrazilLookupService;
use DsApps\LaravelCrm\Support\BrazilianDocument;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrazilLookupTest extends TestCase
{
    public function test_brazilian_documents_are_normalized_and_validated(): void
    {
        $this->assertSame('52998224725', BrazilianDocument::digits('529.982.247-25'));
        $this->assertTrue(BrazilianDocument::valid('cpf', '52998224725'));
        $this->assertTrue(BrazilianDocument::valid('cnpj', '04.252.011/0001-10'));
        $this->assertFalse(BrazilianDocument::valid('cpf', '111.111.111-11'));
    }

    public function test_cnpj_and_postal_code_are_looked_up_with_normalized_values(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cnpj/v1/04252011000110' => Http::response(['cnpj' => '04252011000110', 'razao_social' => 'Empresa Teste']),
            'https://brasilapi.com.br/api/cep/v1/01001000' => Http::response(['cep' => '01001-000', 'logradouro' => 'Praça da Sé', 'uf' => 'SP']),
        ]);

        $lookups = app(BrazilLookupService::class);

        $this->assertSame('Empresa Teste', $lookups->cnpj('04.252.011/0001-10')['legal_name']);
        $this->assertSame('Praça da Sé', $lookups->postalCode('01001-000')['street']);
        Http::assertSentCount(2);
    }
}
