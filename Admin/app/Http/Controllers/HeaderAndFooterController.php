<?php

namespace App\Http\Controllers;
use App\Models\HeaderAndFooter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HeaderAndFooterController extends Controller
{

    public function show()
    {
        $headerAndFooterEntries = HeaderAndFooter::all();
        return view('HeaderAndFooter', compact('headerAndFooterEntries'));
    }

 
  

    public function save(Request $request)
    {
        try {
            $entry = HeaderAndFooter::updateOrCreate(
                ['id' => $request->id], // If ID exists, update, otherwise create
                [
                    'name' => $request->name,
                    'type' => $request->type,
                    'editor_content' => $request->editor_content
                ]
            );
    
            return response()->json(['success' => true, 'message' => 'Header/Footer entry saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error saving header/footer entry: ' . $e->getMessage()], 500);
        }
    }
    

    public function deleteContract($id)
    {
        $HeaderAndFooter = HeaderAndFooter::findOrFail($id); // Find the variable by ID
        $HeaderAndFooter->delete(); // Delete the variable
        return redirect()->back()->with('success', 'HeaderAndFooter deleted successfully'); // Redirect back with success message
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['Header', 'Footer'])], // Validate against specific values
            'editor_content' => 'required|string', // Adjust validation rules as needed
        ]);
    
        // Find the header/footer entry
        $entry = HeaderAndFooter::findOrFail($id);
        
        // Update the entry with the new values
        $entry->update($validatedData);
    
        // Optionally, you can return a response if needed
        return response()->json(['success' => true, 'message' => 'Header/Footer entry updated successfully.']);
    }
    
  
    public function destroy(Entry $entry)
    {
        // Perform validation or additional checks if needed
        
        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully');
    }
    
}
