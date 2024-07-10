<?php

namespace App\Http\Controllers;

use App\Models\SalesListDraft;

use App\Models\Contract;
use App\Models\VariableList; 
use App\Models\Product;
use App\Models\HeaderAndFooter;
use App\Models\contractvariablecheckbox; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
//For the pricelist table
use App\Models\PriceList;
use App\Models\SalesDetails; 
use App\Models\ProductToSales;

class SalesListDraftController extends Controller
{
     

    public function updateVariableJson(Request $request)
    {
        // Retrieve the ID and variableData from the request
        $id = $request->input('id');
        $variableData = $request->input('variableData');

        // Find the record in the sales_list_draft table by ID
        $salesListDraft = SalesListDraft::find($id);

        if (!$salesListDraft) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Update the variable_json column with the new data
        $salesListDraft->variable_json = $variableData;
        $salesListDraft->save();

        return response()->json(['message' => 'Variable JSON data updated successfully']);
    }

 

    public function getVariableJson(Request $request)
    {
        // Get the ID from the request
        $id = $request->input('id');

        // Query the sales_list_draft table based on the ID
        $variableJsonData = SalesListDraft::find($id);

        if (!$variableJsonData) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Return both variable_json and contract_id
        return response()->json([
            'variable_json' => $variableJsonData->variable_json,
            'contract_id' => $variableJsonData->contract_id
        ]);
    }

    // for delete row in sales draft list table
    public function destroy($id)
    {
        $salesListDraft = SalesListDraft::find($id);

        if (!$salesListDraft) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Delete the record
        $salesListDraft->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    public function edit($id)
    {
        // Logic to retrieve and display data for editing
        $salesListDraft = SalesListDraft::find($id);
        $productName = $salesListDraft ? $salesListDraft->product_name : '';   
        $ContractName = $salesListDraft ? $salesListDraft->contract_name : ''; 
        return view('Edit-New-Contracts', ['id' => $id, 'productName' => $productName , 'ContractName' => $ContractName]);
    }

    
 

    public function showAll()
    {
        $sellerName = Auth::user()->name;

        $salesDetail = SalesDetails::where('name', $sellerName)->first();
        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        $salesId = $salesDetail->id;
        $salesName = $salesDetail->name;

        if ($sellerName == $salesName) {
            // Query SalesListDraft table based on $salesId
            $salesListDraft = SalesListDraft::where('sales_id', $salesId)->get();

            return view('Your-Lists', compact('salesListDraft'));
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthorized access']);
    }

     
    public function createNewEntry(Request $request)
    {
        // Get the name of the authenticated user
        $sellerName = Auth::user()->name;

        // Retrieve sales details based on the seller's name
        $salesDetail = SalesDetails::where('name', $sellerName)->first();

        // Check if sales details are found
        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        // Retrieve the sales ID
        $salesId = $salesDetail->id;  

        // Create a new entry in the SalesListDraft table with sales_id filled
        $newEntry = SalesListDraft::create([
            'sales_id' => $salesId,
        ]);

        // Return a success response
        return response()->json(['message' => 'New entry created successfully.', 'entry' => $newEntry]);
    }
   
}