<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Contract;  
use App\Models\SalesDetails; 
use App\Models\ProductToSales;
use App\Models\contractvariablecheckbox;
use App\Models\VariableList;
use App\Models\PriceList;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
 
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Cache;
 
use HelloSign\Client;
use HelloSign\SignatureRequest;
use HelloSign\Signer;
use App\Models\SalesListDraft; // Import your model

use HelloSign\Client as HelloSignClient;
 
use HelloSign\SignatureRequestSigner;
use Twilio\Rest\Client as TwilioClient;
 
use HelloSign\SignerField;
//use Dropbox\Sign\Model\SignerField;
 
use PDF;
use Dompdf\Dompdf;
 
use Dompdf\Options;

use DateTime; 

use TCPDF;
use phpQuery;
 
 
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString; 

 use setasign\Fpdf\Fpdf;
 
 
 use Smalot\PdfParser\Parser;
 
 
 use setasign\Fpdi\PdfParser\PdfParser;
 
   
use Mpdf;

class ProductController extends Controller
{

    //------------------------------

    // working fine only for firma qui photo position
private function processImageTags($htmlContent)
{
    // Define regex patterns for different image tags
    $patterns = [
        'right' => '/<figure class="image image-style-side"><img[^>]*><\/figure>/i',
        'left' => '/<p><img[^>]*><\/p>/i',
        'middle' => '/<figure class="image"><img[^>]*><\/figure>/i'
    ];

    foreach ($patterns as $position => $pattern) {
        preg_match_all($pattern, $htmlContent, $matches);

        foreach ($matches[0] as $imgTag) {
            // Extract src, width, and height attributes
            preg_match('/src="([^"]*)"/i', $imgTag, $srcMatch);
            preg_match('/width="([^"]*)"/i', $imgTag, $widthMatch);
            preg_match('/height="([^"]*)"/i', $imgTag, $heightMatch);

            $src = $srcMatch[1] ?? '';
            $width = $widthMatch[1] ?? '';
            $height = $heightMatch[1] ?? '';

            // Generate the new image tag based on the position
            switch ($position) {
                case 'right':
                    $newImgTag = "<div style=\"text-align: right;\"><img style=\"aspect-ratio:{$width}/{$height};\" src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
                    break;
                case 'left':
                    $newImgTag = "<div style=\"text-align: left;\"><img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
                    break;
                case 'middle':
                default:
                    $newImgTag = "<div style=\"text-align: center;\"><img style=\"aspect-ratio:{$width}/{$height};\" src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
                    break;
            }

            // Replace the old image tag with the new one in the HTML content
            $htmlContent = str_replace($imgTag, $newImgTag, $htmlContent);
        }
    }

    return $htmlContent;
}

   
public function generatePdfforSales(Request $request)
{
    $selectedContract = $request->input('selectedContractId');
    $contract = Contract::select('editor_content')->find($selectedContract);

    if (!$contract) {
        return response()->json(['error' => 'Contract not found'], 404);
    }

    $htmlContent = $contract->editor_content;
    $variableValues = $request->input('variableValues', []);

    // Replace placeholders with actual values from variableValues
    if ($variableValues) {
        foreach ($variableValues as $name => $variable) {
            $placeholder = '%' . $name . '%';
            $value = $variable['value'] ?? null;

            if ($value !== null) {
                if ($variable['type'] === 'Multiple Box') {
                    // Format the multiple box values with bullet points
                    $valueArray = explode(',', $value);
                    $formattedValue = '<ul>';
                    foreach ($valueArray as $item) {
                        $formattedValue .= '<li>' . htmlspecialchars($item) . '</li>';
                    }
                    $formattedValue .= '</ul>';
                    $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
                } else {
                    $htmlContent = str_replace($placeholder, htmlspecialchars($value), $htmlContent);
                }
            }
        }
    }

    $priceValues = $request->input('priceValues', []);

    // Replace $PRICE$ placeholder in HTML content with priceValues details
    if ($priceValues) {
        $formattedPrice = '';

        if (isset($priceValues['dynamicminRange']) && $priceValues['dynamicminRange'] !== null) {
            $totalPrice = htmlspecialchars($priceValues['dynamicminRange']);
        } elseif (isset($priceValues['fixedvalue']) && $priceValues['fixedvalue'] !== null) {
            $totalPrice = htmlspecialchars($priceValues['fixedvalue']);
        } else {
            $totalPrice = 0;
        }

        $currency = $priceValues['currency'];
        $frequency = $priceValues['frequency'];

        $formattedPrice .= "<br><li>Il prezzo totale di {$totalPrice}€ + IVA sarà corrisposto con le seguenti modalità:</li>";

        // Process the payments
        if ($priceValues) {
            $formattedPrice .= '<ul>';

            $maxRange = $priceValues['paymentMaxRange'];
            $vatPercentage = 0.22; // Example VAT percentage
            $includeOnPrice = true;
            $enableVat = true;

            for ($i = 1; $i <= $maxRange; $i++) {
                $amount = $totalPrice / $maxRange;
                $amount = number_format($amount, 2, '.', ''); // Format to 2 decimal places
                $dueDate = $this->getDateByFrequency($frequency, $i);

                $formattedPrice .= "<li>Pagamento {$i} di €{$amount} + IVA entro il {$dueDate}</li>";
            }

            $formattedPrice .= '</ul>';
        }

        $htmlContent = str_replace('$PRICE$', $formattedPrice, $htmlContent);
    }
    $htmlContent = $this->processImageTags($htmlContent);

    // Set options to allow for remote file access
    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->set_option('isHtml5ParserEnabled', true);

    // Update image paths to be absolute URLs
    $htmlContent = str_replace('src="http://localhost:8000/media/', 'src="' . public_path('media') . '/', $htmlContent);

    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfContent = $dompdf->output();
    $filename = 'contract_' . time() . '.pdf';

    // Check if ID is provided
    $id = $request->input('id');

    if ($id) {
        // If ID is provided, save the PDF name inside the record with the provided ID
        $record = SalesListDraft::find($id);

        if ($record) {
            // Delete the existing PDF file, if it exists
            if ($record->selected_pdf_name) {
                Storage::disk('public')->delete('pdf/' . $record->selected_pdf_name);
            }

            // Save the PDF name in the selected_pdf_name column
            $record->update(['selected_pdf_name' => $filename]);
        } else {
            return response()->json(['error' => 'Record not found'], 404);
        }
    } else {
        // If ID is not provided, check if a PDF with the same name exists and delete it
        $existingPdf = SalesListDraft::where('selected_pdf_name', $filename)->first();

        if ($existingPdf) {
            Storage::disk('public')->delete('pdf/' . $filename);
        }

        // Save the PDF name in the selected_pdf_name column of the last row
        $lastRow = SalesListDraft::latest()->first();

        if ($lastRow) {
            $lastRow->update(['selected_pdf_name' => $filename]);
        } else {
            SalesListDraft::create(['selected_pdf_name' => $filename]);
        }
    }

    // Save the PDF file
    Storage::disk('public')->put('pdf/' . $filename, $pdfContent);
    $pdfUrl = Storage::url('pdf/' . $filename);

    // Store the HTML content in session for later use
    session(['html_content' => $htmlContent]);

    return response()->json(['pdf_url' => $pdfUrl]);
}

public function sendDocumentForSignature(Request $request)
{
    $pdfUrl = $request->input('pdfUrl');
    $recipientEmail = $request->input('recipientEmail');
    $recipientName = $request->input('recipientName');
    $recipientMobile = $request->input('recipientMobile');
    $id = $request->input('id');

    // Retrieve the HTML content from session
    $htmlContent = session('html_content');

    if (!$htmlContent) {
        return response()->json(['error' => 'HTML content is missing.'], 400);
    }

    // Process image tags to ensure positions are set
    $htmlContent = $this->processImageTags($htmlContent);

    // Replace all image tags with the specific signature tag while keeping the alignment
    $htmlContent = $this->replaceImageTagsWithSignatureTag($htmlContent);

    // Generate the PDF from the modified HTML content
    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfContent = $dompdf->output();
    $filename = 'contract_' . time() . '.pdf';
    $absolutePath = public_path('pdf/' . $filename);

    // Save the generated PDF file
    file_put_contents($absolutePath, $pdfContent);

    $client = new \HelloSign\Client(env('HELLOSIGN_API_KEY'));
    $signatureRequest = new \HelloSign\SignatureRequest();
    $signatureRequest->setTitle('Please sign this document');
    $signatureRequest->setSubject('Document Signature Request');
    $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    $signatureRequest->addSigner(new \HelloSign\Signer([
        'email_address' => $recipientEmail,
        'name' => $recipientName
    ]));
    $signatureRequest->addFile($absolutePath);

    // Enable Text Tags and optionally hide them
    $signatureRequest->setUseTextTags(true);
    $signatureRequest->setHideTextTags(true); // Optionally set this to true

    try {
        $response = $client->sendSignatureRequest($signatureRequest);
        $envelopeId = $response->getId();
        $signatureUrl = "https://app.hellosign.com/sign/$envelopeId";

        $draftUpdated = false;
        if ($id) {
            $salesListDraft = SalesListDraft::find($id);
            if ($salesListDraft) {
                $salesListDraft->envelope_id = $envelopeId;
                $salesListDraft->recipient_email = $recipientEmail;
                $salesListDraft->status = 'pending';
                $salesListDraft->save();
                $draftUpdated = true;
            }
        }

        if (!$draftUpdated) {
            $lastRow = SalesListDraft::latest()->first();
            if ($lastRow) {
                $lastRow->envelope_id = $envelopeId;
                $lastRow->recipient_email = $recipientEmail;
                $lastRow->status = 'pending';
                $lastRow->save();
            } else {
                $salesListDraft = new SalesListDraft();
                $salesListDraft->envelope_id = $envelopeId;
                $salesListDraft->recipient_email = $recipientEmail;
                $salesListDraft->status = 'pending';
                $salesListDraft->save();
            }
        }

       // $whatsAppSent = $this->sendWhatsAppMessage($recipientMobile, $signatureUrl);

        $responseMessage = [
            'envelope_id' => $envelopeId,
            'email_status' => 'Email sent successfully.',
            //'whatsapp_status' => $whatsAppSent ? 'WhatsApp message sent successfully.' : 'Failed to send WhatsApp message.'
        ];

        return response()->json($responseMessage);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function replaceImageTagsWithSignatureTag($content)
{
    // Define the regex patterns for the different image tags
    $patterns = [
        'right' => '/<div style="text-align: right;">(<img[^>]*>)<\/div>/i',
        'left' => '/<div style="text-align: left;">(<img[^>]*>)<\/div>/i',
        'middle' => '/<div style="text-align: center;">(<img[^>]*>)<\/div>/i'
    ];

    // Replace each pattern with the signer tag while keeping the alignment
    foreach ($patterns as $pattern) {
        $content = preg_replace_callback($pattern, function ($matches) {
            return str_replace($matches[1], '[sig|req|signer1]', $matches[0]);
        }, $content);
    }

    return $content;
}




   // working dropbox signer tag  
    // public function sendDocumentForSignature(Request $request)
    // {
    //     $pdfUrl = $request->input('pdfUrl');
    //     $recipientEmail = $request->input('recipientEmail');
    //     $recipientName = $request->input('recipientName');
    //     $recipientMobile = $request->input('recipientMobile');
    //     $id = $request->input('id');
    //     $absolutePath = public_path($pdfUrl);

    //     if (!file_exists($absolutePath)) {
    //         return response()->json(['error' => 'File does not exist or invalid path.'], 400);
    //     }

    //     $client = new \HelloSign\Client(env('HELLOSIGN_API_KEY'));
    //     $signatureRequest = new \HelloSign\SignatureRequest();
    //     $signatureRequest->setTitle('Please sign this document');
    //     $signatureRequest->setSubject('Document Signature Request');
    //     $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    //     $signatureRequest->addSigner(new \HelloSign\Signer([
    //         'email_address' => $recipientEmail,
    //         'name' => $recipientName
    //     ]));
    //     $signatureRequest->addFile($absolutePath);

    //     // Enable Text Tags and optionally hide them
    //     $signatureRequest->setUseTextTags(true);
    //     $signatureRequest->setHideTextTags(true); // Optionally set this to true

    //     try {
    //         $response = $client->sendSignatureRequest($signatureRequest);
    //         $envelopeId = $response->getId();
    //         $signatureUrl = "https://app.hellosign.com/sign/$envelopeId";

    //         $draftUpdated = false;
    //         if ($id) {
    //             $salesListDraft = SalesListDraft::find($id);
    //             if ($salesListDraft) {
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();
    //                 $draftUpdated = true;
    //             }
    //         }

    //         if (!$draftUpdated) {
    //             $lastRow = SalesListDraft::latest()->first();
    //             if ($lastRow) {
    //                 $lastRow->envelope_id = $envelopeId;
    //                 $lastRow->recipient_email = $recipientEmail;
    //                 $lastRow->status = 'pending';
    //                 $lastRow->save();
    //             } else {
    //                 $salesListDraft = new SalesListDraft();
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();
    //             }
    //         }

    //         $whatsAppSent = $this->sendWhatsAppMessage($recipientMobile, $signatureUrl);

    //         $responseMessage = [
    //             'envelope_id' => $envelopeId,
    //             'email_status' => 'Email sent successfully.',
    //             'whatsapp_status' => $whatsAppSent ? 'WhatsApp message sent successfully.' : 'Failed to send WhatsApp message.'
    //         ];

    //         return response()->json($responseMessage);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    // both email / dropbox and whats app /twilio is working fine separately *****************------------------
    // public function sendDocumentForSignature(Request $request)
    // {
    //     $pdfUrl = $request->input('pdfUrl');
    //     $recipientEmail = $request->input('recipientEmail');
    //     $recipientName = $request->input('recipientName');
    //     $recipientMobile = $request->input('recipientMobile');
    //     $id = $request->input('id');
    //     $absolutePath = public_path($pdfUrl);
 

    //     if (!file_exists($absolutePath)) {
    //         return response()->json(['error' => 'File does not exist or invalid path.'], 400);
    //     }

    //     $client = new \HelloSign\Client(env('HELLOSIGN_API_KEY'));
    //     $signatureRequest = new \HelloSign\SignatureRequest();
    //     $signatureRequest->setTitle('Please sign this document');
    //     $signatureRequest->setSubject('Document Signature Request');
    //     $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    //     $signatureRequest->addSigner(new \HelloSign\Signer([
    //         'email_address' => $recipientEmail,
    //         'name' => $recipientName
    //     ]));
    //     $signatureRequest->addFile($absolutePath);

 
  
    //     $formFields = [
    //         [
    //             'api_id' => 'signature1',
    //             'name' => 'Signature 1',
    //             'type' => 'signature',
    //             'x' => 390,
    //             'y' => 150,
    //             'width' => 133,
    //             'height' => 101,
    //             'required' => true,
    //             'page' => 1,
    //             'signer' => 0
    //         ],
    //         [
    //             'api_id' => 'signature2',
    //             'name' => 'Signature 2',
    //             'type' => 'signature',
    //             'x' => 300,
    //             'y' => 300,
    //             'width' => 133,
    //             'height' => 101,
    //             'required' => true,
    //             'page' => 2,
    //             'signer' => 0
    //         ]
    //     ];

 

    //   //  $signatureRequest->setFormFieldsPerDocument([$formFields]);

    //     try {
    //         $response = $client->sendSignatureRequest($signatureRequest);
    //         $envelopeId = $response->getId();
    //         $signatureUrl = "https://app.hellosign.com/sign/$envelopeId";

    //         $draftUpdated = false;
    //         if ($id) {
    //             $salesListDraft = SalesListDraft::find($id);
    //             if ($salesListDraft) {
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();
    //                 $draftUpdated = true;
    //             }
    //         }

    //         if (!$draftUpdated) {
    //             $lastRow = SalesListDraft::latest()->first();
    //             if ($lastRow) {
    //                 $lastRow->envelope_id = $envelopeId;
    //                 $lastRow->recipient_email = $recipientEmail;
    //                 $lastRow->status = 'pending';
    //                 $lastRow->save();
    //             } else {
    //                 $salesListDraft = new SalesListDraft();
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();
    //             }
    //         }

    //         $whatsAppSent = $this->sendWhatsAppMessage($recipientMobile, $signatureUrl);

    //         $responseMessage = [
    //             'envelope_id' => $envelopeId,
    //             'email_status' => 'Email sent successfully.',
    //             'whatsapp_status' => $whatsAppSent ? 'WhatsApp message sent successfully.' : 'Failed to send WhatsApp message.'
    //         ];

    //         return response()->json($responseMessage);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    // For whats app message method 

    private function sendWhatsAppMessage($recipientMobile, $signatureUrl)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');

        $twilio = new TwilioClient($twilioSid, $twilioAuthToken);

        $message = "Hello, We are from Codice 1%. Here is your contract. Please sign this document: $signatureUrl";

        try {
            $twilio->messages->create(
                "whatsapp:$recipientMobile",
                [
                    'from' => "whatsapp:$twilioWhatsAppNumber",
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp message: ' . $e->getMessage());
            return false;
        }
    }



    

    // working both with hellosign and  twilo whatsapp message  ******************************------------
    // public function sendDocumentForSignature(Request $request)
    // {
    //     $pdfUrl = $request->input('pdfUrl');
    //     $recipientEmail = $request->input('recipientEmail');
    //     $recipientName = $request->input('recipientName');
    //     $recipientMobile = $request->input('recipientMobile'); // Get recipient mobile number
    //     $id = $request->input('id'); // Retrieve the id if provided
    
    //     // Assuming the pdfUrl is a relative path from the public directory
    //     $absolutePath = public_path($pdfUrl);
    
    //     // Verify the file exists
    //     if (!file_exists($absolutePath)) {
    //         return response()->json(['error' => 'File does not exist or invalid path.'], 400);
    //     }
    
    //     $client = new \HelloSign\Client(env('HELLOSIGN_API_KEY'));
    
    //     $signatureRequest = new \HelloSign\SignatureRequest();
    //     $signatureRequest->setTitle('Please sign this document');
    //     $signatureRequest->setSubject('Document Signature Request');
    //     $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
    //     $signatureRequest->addSigner(new \HelloSign\Signer([
    //         'email_address' => $recipientEmail,
    //         'name' => $recipientName
    //     ]));
    //     $signatureRequest->addFile($absolutePath);
    
    //     // Enable test mode
    //     $signatureRequest->enableTestMode();


    //   // Define form fields per document to add signature field using coordinate 

    //     $formFields = [
    //             [
    //                 'api_id' => 'signature1',
    //                 'name' => 'Signature 1',
    //                 'type' => 'signature',
    //                 'x' => 100,
    //                 'y' => 150,
    //                 'page' => 1,
    //                 'signer' => 0
    //             ],
    //             [
    //                 'api_id' => 'signature2',
    //                 'name' => 'Signature 2',
    //                 'type' => 'signature',
    //                 'x' => 200,
    //                 'y' => 300,
    //                 'page' => 2,
    //                 'signer' => 0
    //             ]
    //         ];
        
    //         $signatureRequest->setFormFieldsPerDocument($formFields);
    
    //     try {
    //         $response = $client->sendSignatureRequest($signatureRequest);
    //         $envelopeId = $response->getId();
    //         $signatureUrl = "https://app.hellosign.com/sign/$envelopeId";
    
    //         if ($id) {
    //             // Find and update the existing record by ID
    //             $salesListDraft = SalesListDraft::find($id);
    //             if ($salesListDraft) {
    //                 $salesListDraft->envelope_id = $envelopeId;
    //                 $salesListDraft->recipient_email = $recipientEmail;
    //                 $salesListDraft->status = 'pending';
    //                 $salesListDraft->save();
    
    //                 // Send WhatsApp message and check for success
    //                 if ($this->sendWhatsAppMessage($recipientMobile, $signatureUrl)) {
    //                     return response()->json(['envelope_id' => $envelopeId]);
    //                 } else {
    //                     return response()->json(['error' => 'Failed to send WhatsApp message.'], 500);
    //                 }
    //             }
    //         }
    
    //         // If no ID provided or record not found, update the last row
    //         $lastRow = SalesListDraft::latest()->first();
    //         if ($lastRow) {
    //             $lastRow->envelope_id = $envelopeId;
    //             $lastRow->recipient_email = $recipientEmail;
    //             $lastRow->status = 'pending';
    //             $lastRow->save();
    
    //             // Send WhatsApp message and check for success
    //             if ($this->sendWhatsAppMessage($recipientMobile, $signatureUrl)) {
    //                 return response()->json(['envelope_id' => $envelopeId]);
    //             } else {
    //                 return response()->json(['error' => 'Failed to send WhatsApp message.'], 500);
    //             }
    //         } else {
    //             // If no records exist at all, create a new one
    //             $salesListDraft = new SalesListDraft();
    //             $salesListDraft->envelope_id = $envelopeId;
    //             $salesListDraft->recipient_email = $recipientEmail;
    //             $salesListDraft->status = 'pending';
    //             $salesListDraft->save();
    
    //             // Send WhatsApp message and check for success
    //             if ($this->sendWhatsAppMessage($recipientMobile, $signatureUrl)) {
    //                 return response()->json(['envelope_id' => $envelopeId]);
    //             } else {
    //                 return response()->json(['error' => 'Failed to send WhatsApp message.'], 500);
    //             }
    //         }
    
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    
    // private function sendWhatsAppMessage($recipientMobile, $signatureUrl)
    // {
    //     $twilioSid = env('TWILIO_SID');
    //     $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
    //     $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
    
    //     $twilio = new TwilioClient($twilioSid, $twilioAuthToken);
    
    //     $message = "Hello,We are from Codice 1% .Here is your Contract . please sign this document: $signatureUrl";
    
    //     try {
    //         $twilio->messages->create(
    //             "whatsapp:$recipientMobile",
    //             [
    //                 'from' => "whatsapp:$twilioWhatsAppNumber",
    //                 'body' => $message
    //             ]
    //         );
    //         return true;
    //     } catch (\Exception $e) {
    //         // Handle any errors that may occur
    //         \Log::error('Error sending WhatsApp message: ' . $e->getMessage());
    //         return false;
    //     }
    // }
    
    

// 
    // only for sending email with hello sign dropbox sign working*************************************************
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

    // ********************************  
 

    public function show($id)
    {
        $draft = SalesListDraft::findOrFail($id);
        return response()->json([
            'variable_json' => $draft->variable_json
        ]);
    }


    public function getPriceListsPayment(Request $request)
    {
        $selectedContractId = $request->input('selectedContractId');
        $id = $request->input('id');

        $salesListDraft = SalesListDraft::where('contract_id', $selectedContractId)
                                        ->where('id', $id)
                                        ->first();

        if ($salesListDraft) {
            return response()->json([
                'price_json' => $salesListDraft->price_json
            ]);
        } else {
            return response()->json([
                'message' => 'Data not found'
            ], 404);
        }
    }
    
 
public function updateVariableData(Request $request)
{
    $id = $request->input('id');
    $variableData = json_decode($request->input('variableData'), true);

    $salesListDraft = SalesListDraft::find($id);
    $lastRow = SalesListDraft::latest()->first();

    if ($salesListDraft) {
        $salesListDraft->variable_json = $variableData;
        $salesListDraft->save();

        return response()->json(['message' => 'Variable data updated successfully']);
    } else {
            $lastRow->variable_json = $variableData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
    }
}



    // for save variable json data in SalesListDraft table
    public function savePriceJsonData(Request $request)
    {
        // Get the variable data from the request
        $variableData = $request->input('priceJsonData');
        $id = $request->input('id');

        $lastRow = SalesListDraft::latest()->first();

        if ($id) {
            // If ID is provided, try to find the contract by ID
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                // If contract exists, update its variable_json column with the new data
                $existingContract->update(['price_json' => $variableData]);
                return response()->json(['message' => 'Data saved successfully for the contract with ID ' . $id]);
            } else {
                // If contract with provided ID not found, return an error response
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow->price_json = $variableData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
        }
    }


     // for save variable json data in SalesListDraft table
     public function saveVariableData(Request $request)
    {
        // Get the variable data from the request
        $variableData = $request->input('variableData');
        $id = $request->input('id');

        $lastRow = SalesListDraft::latest()->first();

        if ($id) {
            // If ID is provided, try to find the contract by ID
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                // If contract exists, update its variable_json column with the new data
                $existingContract->update(['variable_json' => $variableData]);
                return response()->json(['message' => 'Data saved successfully for the contract with ID ' . $id]);
            } else {
                // If contract with provided ID not found, return an error response
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow->variable_json = $variableData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
        }
    }
    
    

       // for save variable json data in SalesListDraft table
       public function saveEditedVariableData(Request $request)
       {
               // Get the variable data and the ID from the request
            $variableData = $request->input('variableData');
            $id = $request->input('id');

            // Find the draft in the database by its ID
            $draft = SalesListDraft::find($id);

            if ($draft) {
                // Update the 'variable_json' column with the new variable data
                $draft->variable_json = $variableData;
                $draft->save();  // Save the changes to the database

                // Return a response indicating success
                return response()->json(['message' => 'Variable data updated successfully!'], 200);
            } else {
                // Return a response indicating the draft was not found
                return response()->json(['message' => 'Draft not found'], 404);
            }
       }

    

     // testing method

     public function generateHtmlToPDF()
     {
         $html = '<h1>Generate html to PDF</h1>
                  <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry<p>';
         
         $pdf= PDF::loadHTML($html);
        
         return $pdf->download('invoice.pdf');
        
     }

    public function deletePdf(Request $request)
    {
        // Get the URL of the PDF file to be deleted from the request
        $pdfUrl = $request->input('pdfUrl');

        // Extract the filename from the URL
        $filename = basename($pdfUrl);

        // Delete the PDF file from the storage folder
        Storage::disk('public')->delete('pdf/' . $filename);

        // Return a success response
        return response()->json(['message' => 'PDF deleted successfully']);
    }

 
  
 


    //  work  fine for generate pdf with firma qui photo position
    
    
//     public function generatePdfforSales(Request $request)
// {
//     $selectedContract = $request->input('selectedContractId');
//     $contract = Contract::select('editor_content')->find($selectedContract);

//     if (!$contract) {
//         return response()->json(['error' => 'Contract not found'], 404);
//     }

//     $htmlContent = $contract->editor_content;
//     $variableValues = $request->input('variableValues', []);

//     // Replace placeholders with actual values from variableValues
//     if ($variableValues) {
//         foreach ($variableValues as $name => $variable) {
//             $placeholder = '%' . $name . '%';
//             $value = $variable['value'] ?? null;

//             if ($value !== null) {
//                 if ($variable['type'] === 'Multiple Box') {
//                     // Format the multiple box values with bullet points
//                     $valueArray = explode(',', $value);
//                     $formattedValue = '<ul>';
//                     foreach ($valueArray as $item) {
//                         $formattedValue .= '<li>' . htmlspecialchars($item) . '</li>';
//                     }
//                     $formattedValue .= '</ul>';
//                     $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
//                 } else {
//                     $htmlContent = str_replace($placeholder, htmlspecialchars($value), $htmlContent);
//                 }
//             }
//         }
//     }

//     $priceValues = $request->input('priceValues', []);

//     // Replace $PRICE$ placeholder in HTML content with priceValues details
//     if ($priceValues) {
//         $formattedPrice = '';

//         if (isset($priceValues['dynamicminRange']) && $priceValues['dynamicminRange'] !== null) {
//             $totalPrice = htmlspecialchars($priceValues['dynamicminRange']);
//         } elseif (isset($priceValues['fixedvalue']) && $priceValues['fixedvalue'] !== null) {
//             $totalPrice = htmlspecialchars($priceValues['fixedvalue']);
//         } else {
//             $totalPrice = 0;
//         }

//         $currency = $priceValues['currency'];
//         $frequency = $priceValues['frequency'];

//         $formattedPrice .= "<br><li>Il prezzo totale di {$totalPrice}€ + IVA sarà corrisposto con le seguenti modalità:</li>";

//         // Process the payments
//         if ($priceValues) {
//             $formattedPrice .= '<ul>';

//             $maxRange = $priceValues['paymentMaxRange'];
//             $vatPercentage = 0.22; // Example VAT percentage
//             $includeOnPrice = true;
//             $enableVat = true;

//             for ($i = 1; $i <= $maxRange; $i++) {
//                 $amount = $totalPrice / $maxRange;
//                 $amount = number_format($amount, 2, '.', ''); // Format to 2 decimal places
//                 $dueDate = $this->getDateByFrequency($frequency, $i);

//                 $formattedPrice .= "<li>Pagamento {$i} di €{$amount} + IVA entro il {$dueDate}</li>";
//             }

//             $formattedPrice .= '</ul>';
//         }

//         $htmlContent = str_replace('$PRICE$', $formattedPrice, $htmlContent);
//     }

//     // Process image tags in the HTML content
//     $htmlContent = $this->processImageTags($htmlContent);

//     $dompdf = new Dompdf();
//     $dompdf->set_option('enable_remote', TRUE);
//     $dompdf->setBasePath(public_path()); 
//     $dompdf->loadHtml($htmlContent);
//     $dompdf->setPaper('A4', 'portrait');
//     $dompdf->render();
//     $pdfContent = $dompdf->output();
//     $filename = 'contract_' . time() . '.pdf';

//     // Check if ID is provided
//     $id = $request->input('id');

//     if ($id) {
//         // If ID is provided, save the PDF name inside the record with the provided ID
//         $record = SalesListDraft::find($id);

//         if ($record) {
//             // Delete the existing PDF file, if it exists
//             if ($record->selected_pdf_name) {
//                 Storage::disk('public')->delete('pdf/' . $record->selected_pdf_name);
//             }

//             // Save the PDF name in the selected_pdf_name column
//             $record->update(['selected_pdf_name' => $filename]);
//         } else {
//             return response()->json(['error' => 'Record not found'], 404);
//         }
//     } else {
//         // If ID is not provided, check if a PDF with the same name exists and delete it
//         $existingPdf = SalesListDraft::where('selected_pdf_name', $filename)->first();

//         if ($existingPdf) {
//             Storage::disk('public')->delete('pdf/' . $filename);
//         }

//         // Save the PDF name in the selected_pdf_name column of the last row
//         $lastRow = SalesListDraft::latest()->first();

//         if ($lastRow) {
//             $lastRow->update(['selected_pdf_name' => $filename]);
//         } else {
//             SalesListDraft::create(['selected_pdf_name' => $filename]);
//         }
//     }

//     // Save the PDF file
//     Storage::disk('public')->put('pdf/' . $filename, $pdfContent);
//     $pdfUrl = Storage::url('pdf/' . $filename);

//     return response()->json(['pdf_url' => $pdfUrl]);
// }

 


// working fine only for firma qui photo position
// private function processImageTags($htmlContent)
// {
//     // Define regex patterns for different image tags
//     $patterns = [
//         'right' => '/<figure class="image image-style-side"><img[^>]*><\/figure>/i',
//         'left' => '/<p><img[^>]*><\/p>/i',
//         'middle' => '/<figure class="image"><img[^>]*><\/figure>/i'
//     ];

//     foreach ($patterns as $position => $pattern) {
//         preg_match_all($pattern, $htmlContent, $matches);

//         foreach ($matches[0] as $imgTag) {
//             // Extract src, width, and height attributes
//             preg_match('/src="([^"]*)"/i', $imgTag, $srcMatch);
//             preg_match('/width="([^"]*)"/i', $imgTag, $widthMatch);
//             preg_match('/height="([^"]*)"/i', $imgTag, $heightMatch);

//             $src = $srcMatch[1] ?? '';
//             $width = $widthMatch[1] ?? '';
//             $height = $heightMatch[1] ?? '';

//             // Generate the new image tag based on the position
//             switch ($position) {
//                 case 'right':
//                     $newImgTag = "<div style=\"text-align: right;\"><img style=\"aspect-ratio:{$width}/{$height};\" src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
//                     break;
//                 case 'left':
//                     $newImgTag = "<div style=\"text-align: left;\"><img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
//                     break;
//                 case 'middle':
//                 default:
//                     $newImgTag = "<div style=\"text-align: center;\"><img style=\"aspect-ratio:{$width}/{$height};\" src=\"{$src}\" width=\"{$width}\" height=\"{$height}\"></div>";
//                     break;
//             }

//             // Replace the old image tag with the new one in the HTML content
//             $htmlContent = str_replace($imgTag, $newImgTag, $htmlContent);
//         }
//     }

//     return $htmlContent;
// }

   


// public function generatePdfforSales(Request $request)
// {
//     $selectedContract = $request->input('selectedContractId');
//     $contract = Contract::select('editor_content')->find($selectedContract);

//     if (!$contract) {
//         return response()->json(['error' => 'Contract not found'], 404);
//     }

//     $htmlContent = $contract->editor_content;
//     $variableValues = $request->input('variableValues', []);

//     // Replace placeholders with actual values from variableValues
//     if ($variableValues) {
//         foreach ($variableValues as $name => $variable) {
//             $placeholder = '%' . $name . '%';
//             $value = $variable['value'] ?? null;

//             if ($value !== null) {
//                 if ($variable['type'] === 'Multiple Box') {
//                     // Format the multiple box values with bullet points
//                     $valueArray = explode(',', $value);
//                     $formattedValue = '<ul>';
//                     foreach ($valueArray as $item) {
//                         $formattedValue .= '<li>' . htmlspecialchars($item) . '</li>';
//                     }
//                     $formattedValue .= '</ul>';
//                     $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
//                 } else {
//                     $htmlContent = str_replace($placeholder, htmlspecialchars($value), $htmlContent);
//                 }
//             }
//         }
//     }

//     $priceValues = $request->input('priceValues', []);

//     // Replace $PRICE$ placeholder in HTML content with priceValues details
//     if ($priceValues) {
//         $formattedPrice = '';

//         if (isset($priceValues['dynamicminRange']) && $priceValues['dynamicminRange'] !== null) {
//             $totalPrice = htmlspecialchars($priceValues['dynamicminRange']);
//         } elseif (isset($priceValues['fixedvalue']) && $priceValues['fixedvalue'] !== null) {
//             $totalPrice = htmlspecialchars($priceValues['fixedvalue']);
//         } else {
//             $totalPrice = 0;
//         }

//         $currency = $priceValues['currency'];
//         $frequency = $priceValues['frequency'];

//         $formattedPrice .= "<br><li>Il prezzo totale di {$totalPrice}€ + IVA sarà corrisposto con le seguenti modalità:</li>";

//         // Process the payments
//         if ($priceValues) {
//             $formattedPrice .= '<ul>';

//             $maxRange = $priceValues['paymentMaxRange'];
//             $vatPercentage = 0.22; // Example VAT percentage
//             $includeOnPrice = true;
//             $enableVat = true;

//             for ($i = 1; $i <= $maxRange; $i++) {
//                 $amount = $totalPrice / $maxRange;
//                 $amount = number_format($amount, 2, '.', ''); // Format to 2 decimal places
//                 $dueDate = $this->getDateByFrequency($frequency, $i);

//                 $formattedPrice .= "<li>Pagamento {$i} di €{$amount} + IVA entro il {$dueDate}</li>";
//             }

//             $formattedPrice .= '</ul>';
//         }

//         $htmlContent = str_replace('$PRICE$', $formattedPrice, $htmlContent);
//     }
//     $htmlContent = $this->processImageTags($htmlContent);

//     // Set options to allow for remote file access
//     $options = new Options();
//     $options->set('isRemoteEnabled', true);

//     $dompdf = new Dompdf($options);
//     $dompdf->set_option('isHtml5ParserEnabled', true);

//     // Update image paths to be absolute URLs
//     $htmlContent = str_replace('src="http://localhost:8000/media/', 'src="' . public_path('media') . '/', $htmlContent);
 
 

//     $dompdf->loadHtml($htmlContent);
//     $dompdf->setPaper('A4', 'portrait');
//     $dompdf->render();
//     $pdfContent = $dompdf->output();
//     $filename = 'contract_' . time() . '.pdf';

//     // Check if ID is provided
//     $id = $request->input('id');

//     if ($id) {
//         // If ID is provided, save the PDF name inside the record with the provided ID
//         $record = SalesListDraft::find($id);

//         if ($record) {
//             // Delete the existing PDF file, if it exists
//             if ($record->selected_pdf_name) {
//                 Storage::disk('public')->delete('pdf/' . $record->selected_pdf_name);
//             }

//             // Save the PDF name in the selected_pdf_name column
//             $record->update(['selected_pdf_name' => $filename]);
//         } else {
//             return response()->json(['error' => 'Record not found'], 404);
//         }
//     } else {
//         // If ID is not provided, check if a PDF with the same name exists and delete it
//         $existingPdf = SalesListDraft::where('selected_pdf_name', $filename)->first();

//         if ($existingPdf) {
//             Storage::disk('public')->delete('pdf/' . $filename);
//         }

//         // Save the PDF name in the selected_pdf_name column of the last row
//         $lastRow = SalesListDraft::latest()->first();

//         if ($lastRow) {
//             $lastRow->update(['selected_pdf_name' => $filename]);
//         } else {
//             SalesListDraft::create(['selected_pdf_name' => $filename]);
//         }
//     }

//     // Save the PDF file
//     Storage::disk('public')->put('pdf/' . $filename, $pdfContent);
//     $pdfUrl = Storage::url('pdf/' . $filename);

//     return response()->json(['pdf_url' => $pdfUrl]);
// }

 
    // main working function for generate pdf *************************************--------********************

    // public function generatePdfforSales(Request $request)
    //     {
    //         $selectedContract = $request->input('selectedContractId');
    //         $contract = Contract::select('editor_content')->find($selectedContract);

    //         if (!$contract) {
    //             return response()->json(['error' => 'Contract not found'], 404);
    //         }

    //         $htmlContent = $contract->editor_content;
    //         $variableValues = $request->input('variableValues', []);

    //         // Replace placeholders with actual values from variableValues
    //         if ($variableValues) {
    //             foreach ($variableValues as $name => $variable) {
    //                 $placeholder = '%' . $name . '%';
    //                 $value = $variable['value'] ?? null;

    //                 if ($value !== null) {
    //                     if ($variable['type'] === 'Multiple Box') {
    //                         // Format the multiple box values with bullet points
    //                         $valueArray = explode(',', $value);
    //                         $formattedValue = '<ul>';
    //                         foreach ($valueArray as $item) {
    //                             $formattedValue .= '<li>' . htmlspecialchars($item) . '</li>';
    //                         }
    //                         $formattedValue .= '</ul>';
    //                         $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
    //                     } else {
    //                         $htmlContent = str_replace($placeholder, htmlspecialchars($value), $htmlContent);
    //                     }
    //                 }
    //             }
    //         }

    //         $priceValues = $request->input('priceValues', []);

    //         // Replace $PRICE$ placeholder in HTML content with priceValues details
    //         if ($priceValues) {
    //             $formattedPrice = '';

    //             if (isset($priceValues['dynamicminRange']) && $priceValues['dynamicminRange'] !== null) {
    //                 $totalPrice = htmlspecialchars($priceValues['dynamicminRange']);
    //             } elseif (isset($priceValues['fixedvalue']) && $priceValues['fixedvalue'] !== null) {
    //                 $totalPrice = htmlspecialchars($priceValues['fixedvalue']);
    //             } else {
    //                 $totalPrice = 0;
    //             }

    //             $currency = $priceValues['currency'];
    //             $frequency = $priceValues['frequency'];

    //             $formattedPrice .= "<br><li>Il prezzo totale di {$totalPrice}€ + IVA sarà corrisposto con le seguenti modalità:</li>";

    //             // Process the payments
    //             if ($priceValues) {
    //                 $formattedPrice .= '<ul>';

    //                 $maxRange = $priceValues['paymentMaxRange'];
    //                 $vatPercentage = 0.22; // Example VAT percentage
    //                 $includeOnPrice = true;
    //                 $enableVat = true;

    //                 for ($i = 1; $i <= $maxRange; $i++) {
    //                     $amount = $totalPrice / $maxRange;
    //                     $amount = number_format($amount, 2, '.', ''); // Format to 2 decimal places
    //                     $dueDate = $this->getDateByFrequency($frequency, $i);

    //                     $formattedPrice .= "<li>Pagamento {$i} di €{$amount} + IVA entro il {$dueDate}</li>";
    //                 }

    //                 $formattedPrice .= '</ul>';
    //             }

    //             $htmlContent = str_replace('$PRICE$', $formattedPrice, $htmlContent);
    //         }

    //         // Set options to allow for remote file access
    //     // $dompdf->set_option('enable_remote', TRUE);
    //     // $options->set('isRemoteEnabled', true);

    //         $dompdf = new Dompdf();
    //         $dompdf->set_option('enable_remote', TRUE);
    //         $dompdf->loadHtml($htmlContent);
    //         $dompdf->setPaper('A4', 'portrait');
    //         $dompdf->render();
    //         $pdfContent = $dompdf->output();
    //         $filename = 'contract_' . time() . '.pdf';

    //         // Check if ID is provided
    //         $id = $request->input('id');

    //         if ($id) {
    //             // If ID is provided, save the PDF name inside the record with the provided ID
    //             $record = SalesListDraft::find($id);

    //             if ($record) {
    //                 // Delete the existing PDF file, if it exists
    //                 if ($record->selected_pdf_name) {
    //                     Storage::disk('public')->delete('pdf/' . $record->selected_pdf_name);
    //                 }

    //                 // Save the PDF name in the selected_pdf_name column
    //                 $record->update(['selected_pdf_name' => $filename]);
    //             } else {
    //                 return response()->json(['error' => 'Record not found'], 404);
    //             }
    //         } else {
    //             // If ID is not provided, check if a PDF with the same name exists and delete it
    //             $existingPdf = SalesListDraft::where('selected_pdf_name', $filename)->first();

    //             if ($existingPdf) {
    //                 Storage::disk('public')->delete('pdf/' . $filename);
    //             }

    //             // Save the PDF name in the selected_pdf_name column of the last row
    //             $lastRow = SalesListDraft::latest()->first();

    //             if ($lastRow) {
    //                 $lastRow->update(['selected_pdf_name' => $filename]);
    //             } else {
    //                 SalesListDraft::create(['selected_pdf_name' => $filename]);
    //             }
    //         }

    //         // Save the PDF file
    //         Storage::disk('public')->put('pdf/' . $filename, $pdfContent);
    //         $pdfUrl = Storage::url('pdf/' . $filename);

    //         return response()->json(['pdf_url' => $pdfUrl]);
    //     }

 
//**************** */

private function convertImagePaths($htmlContent)
{
    // Convert all image paths to absolute URLs
    return preg_replace_callback('/<img[^>]+src="([^">]+)"/', function ($matches) {
        $src = $matches[1];
        // If the src is already an absolute URL, return it as is
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            return $matches[0];
        }

        // Otherwise, convert the relative path to an absolute URL
        $absoluteUrl = url($src);
        return str_replace($src, $absoluteUrl, $matches[0]);
    }, $htmlContent);
}

 

    private function getDateByFrequency($frequency, $offset)
    {
        $offset = $offset - 1; // Adjust offset to be zero-based
        $currentDate = new DateTime();
        switch ($frequency) {
            case 'daily':
                $currentDate->modify("+{$offset} day");
                break;
            case 'biweekly':
                $currentDate->modify("+" . ($offset * 14) . " day");
                break;
            case 'weekly':
                $currentDate->modify("+" . ($offset * 7) . " day");
                break;
            case 'monthly':
                $currentDate->modify("+{$offset} month");
                break;
            case 'annually':
                $currentDate->modify("+{$offset} year");
                break;
            default:
                break;
        }
        return $currentDate->format('d/m/Y');
    }
    
    
// 
// working one for genrate pdf 
    // public function generatePdfforSales(Request $request)
    // {
        
    //     $selectedContract = $request->input('selectedContractId');
    //     $contract = Contract::select('editor_content')->find($selectedContract);
    //     if (!$contract) {
            
    //         return response()->json(['error' => 'Contract not found'], 404);
    //     }
    //     $htmlContent = $contract->editor_content;
    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml($htmlContent);
    //     $dompdf->setPaper('A4', 'portrait');
    //     $dompdf->render();
    //     $pdfContent = $dompdf->output();
    //     $filename = 'contract_' . time() . '.pdf';
    //     Storage::disk('public')->put('pdf/' . $filename, $pdfContent);
    //     $pdfUrl = Storage::url('pdf/' . $filename);
    
    //     return response()->json(['pdf_url' => $pdfUrl]);
    // }

 
     // get selected editor content 
     public function geteditorcontent(Request $request)
     {
         // Get the selected contract ID from the request
         $selectedContract = $request->input('selectedContractId');
         
         // Retrieve the contract with the specified ID along with its editor_content field
         $contract = Contract::select('editor_content')->find($selectedContract);
     
         // Check if the contract exists
         if ($contract) {
             // Return the editor_content data
             return response()->json(['editor_content' => $contract->editor_content]);
         } else {
             // If the contract does not exist, return an error response
             return response()->json(['error' => 'Contract not found'], 404);
         }
     }
     


    // get price lists 
    public function getAllPriceLists(Request $request)
    {
        $selectedContract = $request->input('selectedContractId');

        // Query to get the price_id from the contracts table
        $priceId = Contract::where('id', $selectedContract)->value('price_id');

        // Query to get all values from the price_lists table based on the price_id
        $priceLists = PriceList::where('id', $priceId)->first();

        // Return the price lists data
        return response()->json($priceLists);
    }

 
    

    public function getVariablesForEdit(Request $request)
    {
        $contractID = $request->input('selectedContractId');
        $id = $request->input('id');
    
        // Query using Eloquent
        $draft = SalesListDraft::where('contract_id', $contractID)
                               ->where('id', $id)
                               ->first();
    
        if ($draft) {
            return response()->json([
                'success' => true,
                'data' => $draft->variable_json
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No entry found for the specified ID and Contract ID'
            ]);
        }
    }
    


  
    public function getallvariables(Request $request)
        {
            $contractID = $request->input('selectedContractId');
            $id = $request->input('id');

             // update contractID and contractName  into table sales_list_draft last  row id as contract_id and contract_name
              // Query the contracts table to get the contract name
                $contract = Contract::find($contractID);
           
            
                $contractName = $contract->contract_name;


                $id = $request->input('id');
            
                if ($id) {
                    // If ID is provided, try to find the contract by ID
                    $existingContract = SalesListDraft::find($id);
        
                    if ($existingContract) {
                        // If contract exists, update its details
                        $existingContract->update([
                            'contract_id' => $contractID,
                            'contract_name' => $contractName
                        ]);
                    } else {
                        // If contract with provided ID not found, return an error response
                        return response()->json(['status' => 'error', 'message' => 'Contract not found']);
                    }
                } else {
                    $lastRow = SalesListDraft::latest()->first();
                    // If ID is not provided, create a new contract entry
                    $lastRow->update([
                        'contract_id' => $contractID,
                        'contract_name' => $contractName
                    ]);
                }

            // Retrieve distinct variable IDs associated with the given contract ID
            $variableIDs = contractVariableCheckbox::where('ContractID', $contractID)
                            ->distinct('VariableID')
                            ->pluck('VariableID')
                            ->toArray();

            // Query the VariableList table to get all related row values based on the variable IDs
            $variableData = VariableList::whereIn('VariableID', $variableIDs)->get();

            // Return the variable data
            return response()->json(['variableData' => $variableData]);
        }


        


          
        public function getAllEditVariables(Request $request)
        {
            // Retrieve the 'id' from the request
            $id = $request->input('id');
             
            // Find the draft entry in the database using the model
            $draft = SalesListDraft::find($id);
        
            if (!$draft) {
                // If no draft is found, return an error message
                return response()->json(['success' => false, 'message' => 'Draft not found'], 404);
            }
        
            // Get the contract ID from the found draft
            $contractID = $draft->contract_id;
            
            // Retrieve distinct variable IDs associated with the given contract ID from another table
            $variableIDs = ContractVariableCheckbox::where('ContractID', $contractID)
                                ->distinct('VariableID')
                                ->pluck('VariableID')
                                ->toArray();
        
            // Query the VariableList table to get all related row values based on the variable IDs
            $variableData = VariableList::whereIn('VariableID', $variableIDs)->get();
        
            // Return the variable data along with the contract ID
            return response()->json([
                'success' => true,
                'contractID' => $contractID,
                'variableData' => $variableData
            ]);
        }
        


//  public function getallvariables(Request $request)
//     {
//         $contractID = $request->input('selectedContractId');

//         // Retrieve distinct variable IDs associated with the given contract ID
//         $variableIDs = contractVariableCheckbox::where('ContractID', $contractID)
//                         ->distinct('VariableID')
//                         ->pluck('VariableID')
//                         ->toArray();

      

//        return response()->json(['variableIDs' => $variableIDs]);
//     }



    // ProductController.php
    public function getContracts(Request $request)
    {
        $productName = $request->input('product_name');

        $product = Product::where('product_name', $productName)->first();
 

        $productId = $product->id;

        // Retrieve the contract ID from the request
        $id = $request->input('id');

        if ($id) {
            // If ID is provided, try to find the contract by ID
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                // If contract exists, update its details
                $existingContract->update([
                    'product_id' => $productId,
                    'product_name' => $productName
                ]);
            } else {
                // If contract with provided ID not found, return an error response
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow = SalesListDraft::latest()->first();
            // If ID is not provided, create a new contract entry
            $lastRow->update([
                'product_id' => $productId,
                'product_name' => $productName
            ]);
        }

        // Query the contracts table to get all contracts relevant to the product ID
        $contracts = Contract::where('product_id', $productId)->get(['id', 'contract_name']);

        if ($contracts->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Contracts not found']);
        }

        // Prepare the response data
        $contractData = $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'contract_name' => $contract->contract_name
            ];
        });

        return response()->json(['status' => 'success', 'contracts' => $contractData]);
    }


    public function getProducts(Request $request)
    {
        $sellerName = $request->input('seller_name');
        
        // Get the user's sales details based on the seller name
        $salesDetail = SalesDetails::where('name', $sellerName)->first();

        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        // Get the sales ID from the sales details
        $salesId = $salesDetail->id; // Here, $salesId will be 2 

        // Get all product IDs associated with the sales ID
        $productToSales = ProductToSales::where('sales_id', $salesId)->get();    

        // Here, $productIds will be an array containing 1 and 3
        $productIds = $productToSales->pluck('product_id');

        // Get the product details for each product ID
        $products = Product::whereIn('id', $productIds)->get();

        if ($products->isNotEmpty()) {
            // Extract product names
            // Assuming the product name column is 'product_name' in your 'products' table
            $productNames = $products->pluck('product_name')->toArray(); 

            // Here, $productNames will be an array containing 'lufy producta' and 'my preoduct'
            
            return response()->json(['status' => 'success', 'products' => $productNames]);
        }
        
        return response()->json(['status' => 'error', 'message' => 'Product not found']);
    }


    public function index()
    {
        $products = Product::all();
        return view('ProductList', compact('products'));
    }


    public function deleteproduct($id)
    {
        $Product = Product::findOrFail($id); // Find the variable by ID
        $Product->delete(); // Delete the variable
        return redirect()->back()->with('success', 'Variable deleted successfully'); // Redirect back with success message
    }

    public function saveProduct(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
        
            'productName' => 'required',
            'description' => 'required',
    
        
        ]);

    // Create a new product instance
    $product = new Product;
 
    $product->product_name = $validatedData['productName'];
    $product->description = $validatedData['description'];
   
 

    // Save the product to the database
    $product->save();

    // Optionally, you can return a response or redirect to another page
    return response()->json(['message' => 'Product saved successfully']);
    }

 
    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'product_name' => 'required|string',
            'description' => 'required|string',
            
        ]);
    
        // Find the variable by ID
        $Product = Product::findOrFail($id);
    
        // Update variable details
        $Product->product_name = $request->input('product_name');
        $Product->description = $request->input('description');
    
        // Check if Description field is provided
        if ($request->has('description')) {
            $Product->description = $request->input('description');
        }
    
        // Save the updated variable
        $Product->save();
    
        // Return a response indicating success
        return response()->json(['success' => true, 'message' => 'Variable updated successfully']);
 
    }

}



