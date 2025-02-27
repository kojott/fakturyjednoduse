<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = auth()->user()->customers()->orderBy('company_name')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ico' => 'required|string|max:10',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'country' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:20',
            'note' => 'nullable|string',
        ]);

        // If ICO is provided, we can validate it against ARES API
        if ($request->filled('ico')) {
            try {
                $aresData = $this->validateAres($request->ico);
                
                // If the form was submitted with empty fields but we have ARES data,
                // we can auto-fill those fields
                if (empty($request->company_name) && !empty($aresData['name'])) {
                    $validated['company_name'] = $aresData['name'];
                }
                
                if (empty($request->address) && !empty($aresData['address'])) {
                    $validated['address'] = $aresData['address'];
                }
                
                if (empty($request->city) && !empty($aresData['city'])) {
                    $validated['city'] = $aresData['city'];
                }
                
                if (empty($request->zip_code) && !empty($aresData['zip_code'])) {
                    $validated['zip_code'] = $aresData['zip_code'];
                }
                
            } catch (\Exception $e) {
                return back()->withErrors(['ico' => 'Nepodařilo se ověřit IČO v systému ARES: ' . $e->getMessage()]);
            }
        }

        $validated['user_id'] = auth()->id();
        
        Customer::create($validated);
        
        return redirect()->route('customers.index')
                        ->with('success', 'Zákazník byl úspěšně vytvořen.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        
        $validated = $request->validate([
            'ico' => 'required|string|max:10',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'country' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:20',
            'note' => 'nullable|string',
        ]);
        
        // If ICO has changed, validate it against ARES API
        if ($request->filled('ico') && $customer->ico !== $request->ico) {
            try {
                $aresData = $this->validateAres($request->ico);
                
                // If the form was submitted with empty fields but we have ARES data,
                // we can auto-fill those fields
                if (empty($request->company_name) && !empty($aresData['name'])) {
                    $validated['company_name'] = $aresData['name'];
                }
                
                if (empty($request->address) && !empty($aresData['address'])) {
                    $validated['address'] = $aresData['address'];
                }
                
                if (empty($request->city) && !empty($aresData['city'])) {
                    $validated['city'] = $aresData['city'];
                }
                
                if (empty($request->zip_code) && !empty($aresData['zip_code'])) {
                    $validated['zip_code'] = $aresData['zip_code'];
                }
                
            } catch (\Exception $e) {
                return back()->withErrors(['ico' => 'Nepodařilo se ověřit IČO v systému ARES: ' . $e->getMessage()]);
            }
        }
        
        $customer->update($validated);
        
        return redirect()->route('customers.index')
                        ->with('success', 'Zákazník byl úspěšně aktualizován.');
    }
    
    /**
     * AJAX endpoint for validating ICO and retrieving company data
     */
    public function validateIco(Request $request)
    {
        $request->validate([
            'ico' => 'required|string|max:10',
        ]);
        
        try {
            $data = $this->validateAres($request->ico);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);
        
        // Check if customer has any invoices
        if ($customer->invoices()->count() > 0) {
            return back()->withErrors(['delete' => 'Nelze smazat zákazníka, který má přiřazené faktury.']);
        }
        
        $customer->delete();
        
        return redirect()->route('customers.index')
                        ->with('success', 'Zákazník byl úspěšně smazán.');
    }

    /**
     * Validate company ICO through ARES API
     */
    private function validateAres($ico)
    {
        // New ARES API URL (JSON-based)
        $url = "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/" . $ico;
        
        try {
            $response = Http::get($url);
            
            if ($response->successful()) {
                // Parse JSON response
                $data = $response->json();
                
                // Check if the response contains valid company data
                if (empty($data) || !isset($data['ico'])) {
                    throw new \Exception('IČO nenalezeno v systému ARES.');
                }
                
                // Verify that the returned ICO matches the requested one
                if ((int)$data['ico'] !== (int)$ico) {
                    throw new \Exception('Vrácené IČO neodpovídá požadovanému.');
                }
                
                // Extract company info for potential auto-fill
                $address = '';
                if (isset($data['sidlo'])) {
                    $sidlo = $data['sidlo'];
                    $addressParts = [];
                    
                    if (isset($sidlo['ulice'])) $addressParts[] = $sidlo['ulice'];
                    if (isset($sidlo['cisloDomovni'])) $addressParts[] = $sidlo['cisloDomovni'];
                    if (isset($sidlo['cisloOrientacni'])) $addressParts[] = $sidlo['cisloOrientacni'];
                    
                    $address = implode(' ', $addressParts);
                }
                
                return [
                    'name' => $data['obchodniJmeno'] ?? $data['nazev'] ?? '',
                    'address' => $address,
                    'city' => $data['sidlo']['obec'] ?? '',
                    'zip_code' => $data['sidlo']['psc'] ?? '',
                ];
            } else {
                // Handle HTTP error
                $statusCode = $response->status();
                if ($statusCode == 404) {
                    throw new \Exception('IČO nenalezeno v systému ARES.');
                } else {
                    throw new \Exception("Chyba při komunikaci s ARES API (HTTP $statusCode).");
                }
            }
        } catch (\Exception $e) {
            // Re-throw the exception with a more descriptive message
            throw new \Exception('Nepodařilo se ověřit IČO v systému ARES: ' . $e->getMessage());
        }
    }
}
