<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use HelloSign\Client;
// use HelloSign\SignatureRequest;
// use HelloSign\Signer;
use App\Models\SalesListDraft; // Import your model

 
// use HelloSign\Configuration;
// use HelloSign\SignatureRequestApi;


use Dropbox\Sign\Client;
use Dropbox\Sign\Configuration;
use Dropbox\Sign\Api\SignatureRequestApi;
use Dropbox\Sign\Model\SignatureRequest;
use Dropbox\Sign\Model\Signer;
use Dropbox\Sign\Api\EmbeddedApi;
 
use Dropbox\Sign\ApiException;
 
use Dropbox\Sign\Model\SignatureRequestCreateEmbeddedRequest;
use Dropbox\Sign\Model\SignatureRequestGetResponse;
use Dropbox\Sign\Model\SubSignatureRequestSigner;
use Dropbox\Sign\Model\SubSigningOptions;
 


class SignatureController extends Controller
{
    
      

    public function checkSignatureStatus()
    {
        $config = \Dropbox\Sign\Configuration::getDefaultConfiguration();
        $config->setUsername(env('HELLOSIGN_API_KEY'));

        $signatureRequestApi = new \Dropbox\Sign\Api\SignatureRequestApi($config);

        $salesListDrafts = SalesListDraft::all();

        $statuses = [];

        foreach ($salesListDrafts as $salesListDraft) {
            $envelopeId = $salesListDraft->envelope_id;

            // Check if envelopeId is not null or empty
            if (empty($envelopeId)) {
                $statuses[$salesListDraft->id] = 'no envelope id';
                continue;
            }

            try {
                $result = $signatureRequestApi->signatureRequestGet($envelopeId);
                $signatureRequest = $result->getSignatureRequest();

                $allStatuses = [];
                foreach ($signatureRequest->getSignatures() as $signature) {
                    $allStatuses[] = $signature->getStatusCode();
                }

                // Determine the overall status
                $overallStatus = 'pending';
                if (in_array('declined', $allStatuses)) {
                    $overallStatus = 'declined';
                } elseif (in_array('signed', $allStatuses)) {
                    $overallStatus = 'signed';
                } elseif (in_array('viewed', $allStatuses)) {
                    $overallStatus = 'viewed';
                } elseif (in_array('sent', $allStatuses)) {
                    $overallStatus = 'pending';
                }

                // Update the status in the database
                $salesListDraft->status = $overallStatus;
                $salesListDraft->save();

                // Add to statuses array for response
                $statuses[$envelopeId] = $overallStatus;

            } catch (\Exception $e) {
                // Handle individual errors without stopping the entire process
                $statuses[$envelopeId] = 'error';
                \Log::error("Error checking status for envelope ID $envelopeId: " . $e->getMessage());
            }
        }

        return response()->json(['statuses' => $statuses]);
    }

        


 

    // working one to send with hello sign
    // public function sendDocumentForSignature(Request $request)
    // {
    //     $pdfUrl = $request->input('pdfUrl');
    //     $recipientEmail = $request->input('recipientEmail');
    //     $recipientName = $request->input('recipientName');
    //     $id = $request->input('id'); // Retrieve the id if provided

    //     // Assuming the pdfUrl is a relative path from the public directory
    //     $absolutePath = public_path($pdfUrl);

    //     // Verify the file exists
    //     if (!file_exists($absolutePath)) {
    //         return response()->json(['error' => 'File does not exist or invalid path.'], 400);
    //     }

    //     $client = new Client(env('HELLOSIGN_API_KEY'));

    //     $signatureRequest = new SignatureRequest();
    //     $signatureRequest->setTitle('Please sign this document');
    //     $signatureRequest->setSubject('Document Signature Request');
    //     $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    //     $signatureRequest->addSigner(new Signer([
    //         'email_address' => $recipientEmail,
    //         'name' => $recipientName
    //     ]));
    //     $signatureRequest->addFile($absolutePath);

    //     // Enable test mode
    //     $signatureRequest->enableTestMode();

    //     try {
    //         $response = $client->sendSignatureRequest($signatureRequest);
    //         $envelopeId = $response->getId();

    //         if ($id) {
    //             // Find and update the existing record by ID
    //             $salesListDraft = SalesListDraft::find($id);
    //             if ($salesListDraft) {
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();

    //                 return response()->json(['envelope_id' => $envelopeId]);
    //             }
    //         }

    //         // If no ID provided or record not found, update the last row
    //         $lastRow = SalesListDraft::latest()->first();
    //         if ($lastRow) {
    //             $lastRow->envelope_id = $envelopeId;
    //             $lastRow->recipient_email = $recipientEmail;
    //             $lastRow->status = 'pending';
    //             $lastRow->save();

    //             return response()->json(['envelope_id' => $envelopeId]);
    //         } else {
    //             // If no records exist at all, create a new one
    //             $salesListDraft = new SalesListDraft();
    //             $salesListDraft->envelope_id = $envelopeId;
    //             $salesListDraft->recipient_email = $recipientEmail;
    //             $salesListDraft->status = 'pending';
    //             $salesListDraft->save();

    //             return response()->json(['envelope_id' => $envelopeId]);
    //         }

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }




    // public function checkSignatureStatus()
    // {
    //     $config = \Dropbox\Sign\Configuration::getDefaultConfiguration();
    //     $config->setUsername(env('HELLOSIGN_API_KEY'));
    
    //     $signatureRequestApi = new \Dropbox\Sign\Api\SignatureRequestApi($config);
    
    //     $salesListDrafts = SalesListDraft::all();
    
    //     $statuses = [];
    
    //     foreach ($salesListDrafts as $salesListDraft) {
    //         $envelopeId = $salesListDraft->envelope_id;
    
    //         // Check if envelopeId is not null or empty
    //         if (empty($envelopeId)) {
    //             $statuses[$salesListDraft->id] = 'no envelope id';
    //             continue;
    //         }
    
    //         try {
    //             $result = $signatureRequestApi->signatureRequestGet($envelopeId);
    //             $signatureRequest = $result->getSignatureRequest();
    
    //             $allStatuses = [];
    //             foreach ($signatureRequest->getSignatures() as $signature) {
    //                 $allStatuses[] = $signature->getStatusCode();
    //             }
    
    //             // Determine the overall status
    //             $overallStatus = 'pending';
    //             if (in_array('declined', $allStatuses)) {
    //                 $overallStatus = 'declined';
    //             } elseif (!in_array('pending', $allStatuses) && !in_array('unsigned', $allStatuses)) {
    //                 $overallStatus = 'signed';
    //             }
    
    //             // Update the status in the database
    //             $salesListDraft->status = $overallStatus;
    //             $salesListDraft->save();
    
    //             // Add to statuses array for response
    //             $statuses[$envelopeId] = $overallStatus;
    
    //         } catch (\Exception $e) {
    //             // Handle individual errors without stopping the entire process
    //             $statuses[$envelopeId] = 'error';
    //             \Log::error("Error checking status for envelope ID $envelopeId: " . $e->getMessage());
    //         }
    //     }
    
    //     return response()->json(['statuses' => $statuses]);
    // }
    

     
    // public function checkSignatureStatus(Request $request)
    // {
    //     $envelopeId = $request->input('envelope_id');

    //     $config = Configuration::getDefaultConfiguration();
    //     $config->setUsername(env('HELLOSIGN_API_KEY'));

    //     $signatureRequestApi = new SignatureRequestApi($config);

    //     try {
    //         $result = $signatureRequestApi->signatureRequestGet($envelopeId);
    //         $signatureRequest = $result->getSignatureRequest();

    //         $allStatuses = [];
    //         foreach ($signatureRequest->getSignatures() as $signature) {
    //             $allStatuses[] = $signature->getStatusCode();
    //         }

    //         // Determine the overall status
    //         $overallStatus = 'pending';
    //         if (in_array('declined', $allStatuses)) {
    //             $overallStatus = 'declined';
    //         } elseif (!in_array('pending', $allStatuses) && !in_array('unsigned', $allStatuses)) {
    //             $overallStatus = 'signed';
    //         }

    //         // Update the status in the database
    //         $salesListDraft = SalesListDraft::where('envelope_id', $envelopeId)->first();
    //         if ($salesListDraft) {
    //             $salesListDraft->status = $overallStatus;
    //             $salesListDraft->save();
    //         }

    //         return response()->json(['status' => $overallStatus]);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    
    

    // public function checkSignatureStatus(Request $request)
    // {
    //     $envelopeId = $request->input('envelope_id');

    //     $client = new Client(env('HELLOSIGN_API_KEY'));

    //     try {
    //         $response = $client->getSignatureRequest($envelopeId);
    //         $signatureRequest = $response->getSignatureRequest();
    //         $signatureStatus = $signatureRequest->getSignatures()[0]->getStatusCode();

    //         // Update the status in the database
    //         $salesListDraft = SalesListDraft::where('envelope_id', $envelopeId)->first();
    //         if ($salesListDraft) {
    //             $salesListDraft->status = $signatureStatus;
    //             $salesListDraft->save();
    //         }

    //         return response()->json(['status' => $signatureStatus]);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

 

    // main working one
    // public function sendDocumentForSignature(Request $request)
    // {
    //     $pdfUrl = $request->input('pdfUrl');
    //     $recipientEmail = $request->input('recipientEmail');
    //     $recipientName = $request->input('recipientName');

    //     // Assuming the pdfUrl is a relative path from the public directory
    //     $absolutePath = public_path($pdfUrl);

    //     // Verify the file exists
    //     if (!file_exists($absolutePath)) {
    //         return response()->json(['error' => 'File does not exist or invalid path.'], 400);
    //     }

    //     $client = new Client(env('HELLOSIGN_API_KEY'));

    //     $signatureRequest = new SignatureRequest();
    //     $signatureRequest->setTitle('Please sign this document');
    //     $signatureRequest->setSubject('Document Signature Request');
    //     $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    //     $signatureRequest->addSigner(new Signer([
    //         'email_address' => $recipientEmail,
    //         'name' => $recipientName
    //     ]));
    //     $signatureRequest->addFile($absolutePath);

    //     // Enable test mode
    //     $signatureRequest->enableTestMode();

    //     try {
    //         $response = $client->sendSignatureRequest($signatureRequest);
    //         return response()->json(['envelope_id' => $response->getId()]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    
}


