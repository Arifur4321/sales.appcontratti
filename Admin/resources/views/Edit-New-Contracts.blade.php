@extends('layouts.master')
@section('title')
    @lang('translation.Variable-List')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Projects
        @endslot
        @slot('title')
      Edit Sales List 
        @endslot
    @endcomponent
 
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> 

 <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
 
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js" ></script>
<script src="{{ asset('js/ckeditor/build/ckeditor.js') }}"></script>
 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
   
<div class="row">
    <div class="col-7">
    @if (Auth::check())
        <h6> Seller Name : {{ Auth::user()->name }}</h6> <br>
    @endif
        <div class="mb-3">
             <div class="input-group">
                <label class="input-group-text" for="Product">Product :</label>
                <select class="form-select" id="frequency" name="frequency">
                    <option value="" selected>Select Product</option>
                    @if(!empty($productName))
                        <option value="{{ $productName }}" selected>{{ $productName }}</option>
                    @endif
                    <!-- Other options will be dynamically added here -->
                </select>
             </div>
        </div>
    </div>
</div>

<!-- Contract select element (Initially hidden)  -->
<div class="row" id="contractRow" >
    <div class="col-7">
        <div class="mb-3">
            <div class="input-group">
                <label class="input-group-text" for="Contract">Contract:</label>
                <select class="form-select" id="Contract" name="Contract">
                  <option value="" selected>Select Contract</option>
                  @if(!empty($ContractName))
                        <option value="{{ $ContractName }}" selected>{{ $ContractName }}</option>
                    @endif
                  
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-7" id="variableContainer" style="overflow-y: scroll;">
        <div class="table-responsive">
            <table id="sales-variable" class="table">
                <thead>
                    <tr>
                        <th>Variable Name</th>
                        <th>Variable Label Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- for appearing another table -->
        <div class="row mt-3">
            <div class="col-12" id="ImpostaTable1"></div>
        </div>

        <div class="row mt-3">
                <div class="col-6"></div>
                    <div class="col-6 text-right">
                        <button type="button" class="btn btn-primary" id="updateButton">Update</button>
                        <button type="button" class="btn btn-primary ml-2" id="mytestButton">Preview&Send</button>
                    </div>
            </div>
        </div>
</div>


 <!--for toast -->
      
<div id="liveToast" class="toast fade hide" role="alert" aria-live="assertive" aria-atomic="true"   style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
    <div class="toast-header">
        <img src="" alt="" class="me-2" height="18">
        <strong class="me-auto">Information</strong>
        <small>Few mins ago</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>

<!-- for pdf modal -->
<!-- <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#myModal">Standard modal</button> -->
 




<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Preview PDF</h5>
            </div>
            <div class="col-8"><br>
                <h6>Receiver/Customer Info</h6><br>
                <form id="recipientForm" onsubmit="return false;">
                     <div class="col-10">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="email-addon">Email</span>
                            <input    type="email" class="form-control" id="recipientEmail" placeholder="Enter email" aria-label="Email" aria-describedby="email-addon" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="mobile-addon">Mobile Number :</span>
                            <input type="tel" class="form-control" id="recipientMobile" placeholder="Enter mobile number" aria-label="Mobile Number">
                        </div>
                     </div>
                    
                </form>
            </div>
            <div class="modal-body" style="height: 60vh; overflow-y: auto;">
                <!-- PDF content will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="sendButton" data-bs-dismiss="modal" disabled>Send</button>
            </div>
        </div>
    </div>
</div>


<style>
 .noUi-tooltip {
    display: block !important;
    background: none !important;
    border: none !important;
    color: black !important;
    font-size: 12px !important;
    padding: 0 !important;
    position: absolute;
    top: -25px !important;
    left: 50%;
    transform: translateX(-50%);
}
</style>


<script>
    
    document.addEventListener("DOMContentLoaded", function() {
        var input = document.querySelector("#recipientMobile");
        var iti = window.intlTelInput(input, {
            initialCountry: "it",
            preferredCountries: ["us", "gb", "ca", "au"],
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        // Set the initial country code
        var initialCountryData = iti.getSelectedCountryData();
        input.value = "+" + initialCountryData.dialCode;

        // Update the input field on country change
        input.addEventListener("countrychange", function() {
            var countryCode = iti.getSelectedCountryData().dialCode;
            input.value = "+" + countryCode;
        });
    });
 

</script>

<!--  
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Preview PDF</h5>
            </div>
            <div class="col-8"><br>
                <h6>Receiver/Customer Info</h6><br>
                <form id="recipientForm" onsubmit="return false;">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="email-addon">Email</span>
                        <input type="email" class="form-control" id="recipientEmail" placeholder="Enter email" aria-label="Email" aria-describedby="email-addon" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="mobile-addon">Mobile Number</span>
                        <input type="text" class="form-control" placeholder="Enter mobile number" aria-label="Mobile Number" aria-describedby="mobile-addon">
                    </div>
                </form>
            </div>
            <div class="modal-body" style="height: 60vh; overflow-y: auto;">
   
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="sendButton"  data-bs-dismiss="modal" disabled>Send</button>
            </div>
        </div>
    </div>
</div> -->

<script>

    $(document).ready(function() {

        setInterval(function() {
            checkSignatureStatus();
        }, 600000); // 600,000 milliseconds = 10 minutes
   
    function checkEmailValidity() {
        var emailInput = $('#recipientEmail');
        var sendButton = $('#sendButton');
        if (emailInput[0].checkValidity()) {
            sendButton.prop('disabled', false);
        } else {
            sendButton.prop('disabled', true);
        }
    }

 
    $('#recipientEmail').on('input', function() {
        checkEmailValidity();
    });

    
    checkEmailValidity();


    $('#sendButton').on('click', function() {
        console.log('Send button clicked');
        var pdfUrl = $('.modal-body embed').attr('src');
        var recipientEmail = $('#recipientEmail').val();
        var recipientMobile = $('#recipientMobile').val();
        var recipientName = "Recipient"; // You can add an input field for recipientName if needed

        console.log('PDF URL:', pdfUrl);
        console.log('Recipient Email:', recipientEmail);
        console.log('Recipient Mobile:', recipientMobile);

        sendDocumentForSignature(pdfUrl, recipientEmail, recipientName, recipientMobile);
    });

    function sendDocumentForSignature(pdfUrl, recipientEmail, recipientName, recipientMobile) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var id = window.location.pathname.split('/').pop(); // Extract ID from the URL

        console.log('Sending document for signature');

        $.ajax({
            url: '/send-document-for-signature',
            type: 'POST',
            data: {
                pdfUrl: pdfUrl,
                recipientEmail: recipientEmail,
                recipientName: recipientName,
                recipientMobile: recipientMobile,
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                console.log('Document sent for signature, envelope ID:', response.envelope_id);

                Swal.fire({
                    title: 'Success!',
                    text: 'Document sent successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });

                // Start checking the signature status periodically
                startPeriodicStatusCheck(response.envelope_id);
            },
            error: function(xhr, status, error) {
                console.error('Error sending document for signature:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Error sending document: ' + error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }



 
    // $('#sendButton').on('click', function() {
    //     var pdfUrl = $('.modal-body embed').attr('src');
    //     var recipientEmail = $('#recipientEmail').val();
    //     var recipientName = "Recipient";  

    //     sendDocumentForSignature(pdfUrl, recipientEmail, recipientName);
    // });

    // function sendDocumentForSignature(pdfUrl, recipientEmail, recipientName) {
    //     var csrfToken = $('meta[name="csrf-token"]').attr('content');
    //     var id = window.location.pathname.split('/').pop(); // Extract ID from the URL

    //     $.ajax({
    //         url: '/send-document-for-signature',
    //         type: 'POST',
    //         data: {
    //             pdfUrl: pdfUrl,
    //             recipientEmail: recipientEmail,
    //             recipientName: recipientName,
    //             id: id
    //         },
    //         headers: {
    //             'X-CSRF-TOKEN': csrfToken
    //         },
    //         success: function(response) {
    //             console.log('Document sent for signature, envelope ID:', response.envelope_id);
            
    //             Swal.fire({
    //                 title: 'Success!',
    //                 text: 'Document sent successfully!',
    //                 icon: 'success',
    //                 confirmButtonText: 'OK'
    //             });

    //          //   checkSignatureStatus(response.envelope_id);
             
    //             checkSignatureStatus(response.envelope_id);
       
            
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error sending document for signature:', error);
    //             Swal.fire({
    //                 title: 'Error!',
    //                 text: 'Error sending document: ' + error,
    //                 icon: 'error',
    //                 confirmButtonText: 'OK'
    //             });
    //         }
    //     });
    // }


    
    function checkSignatureStatus() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: '/check-signature-status',
            type: 'POST',
            
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                console.log('Signature status:', response.status);

                // Swal.fire({
                //     title: 'Status',
                //     text: 'Signature status: ' + response.status,
                //     icon: 'info',
                //     confirmButtonText: 'OK'
                // });

                if (response.status === 'signed' || response.status === 'declined') {
                    clearInterval(signatureCheckInterval);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking signature status:', error);

                // Swal.fire({
                //     title: 'Error!',
                //     text: 'Error checking signature status: ' + error,
                //     icon: 'error',
                //     confirmButtonText: 'OK'
                // });
            }
        });
    }

    // function checkSignatureStatus(envelopeId) {
    //     var csrfToken = $('meta[name="csrf-token"]').attr('content');

    //     $.ajax({
    //         url: '/check-signature-status',
    //         type: 'POST',
    //         data: {
    //             envelope_id: envelopeId
    //         },
    //         headers: {
    //             'X-CSRF-TOKEN': csrfToken
    //         },
    //         success: function(response) {
    //             console.log('Signature status:', response.status);

    //             Swal.fire({
    //                 title: 'Status',
    //                 text: 'Signature status: ' + response.status,
    //                 icon: 'info',
    //                 confirmButtonText: 'OK'
    //             });

    //             if (response.status !== 'signed' && response.status !== 'declined') {
    //                 // Continue polling if the document is not yet signed or declined
    //                 // setTimeout(function() {
    //                 //     checkSignatureStatus(envelopeId);
    //                 // }, 60000); // Check every minute

    //                 setInterval(function() {
    //                     checkSignatureStatus(response.envelope_id);
    //                 }, 5000); // Check every 5 seconds
                            
    //                     }
            
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error checking signature status:', error);

    //             Swal.fire({
    //                 title: 'Error!',
    //                 text: 'Error checking signature status: ' + error,
    //                 icon: 'error',
    //                 confirmButtonText: 'OK'
    //             });
    //         }
    //     });
    // }


});

$(document).ready(function() {
    var UrlID = window.location.pathname.split('/').pop();
    var id = UrlID;
    var LocalvariableJson;
    var LocalsingleLineText;
    var MultipleLineText;
    var LocalMultipleBox;
    var LocalSingleBox;
    var Localdates;

    var changedValue;
    var priceJsonData = {};  

    function collectPriceValues() {
    var priceValues = {
        dynamicminRange: priceJsonData.dynamicminRange || null,
        fixedvalue: priceJsonData.fixedvalue || null,
        paymentMinRange: priceJsonData.paymentMinRange,
        paymentMaxRange: priceJsonData.paymentMaxRange,
        currency : priceJsonData.currency,
        frequency : priceJsonData.frequency,

        payments: []
    };

    $('#sales-variable tbody tr').each(function(index, row) {
        var cells = $(row).find('td');
        if (cells.length > 1) {
            var payment = {
                description: cells.eq(0).text(),
                amount: parseFloat(cells.eq(1).find('input').val()),
                vatIncluded: cells.eq(2).text(),
                dueDate: cells.eq(4).find('input').val() || cells.eq(4).text()
            };
            priceValues.payments.push(payment);
        }
    });

    console.log('Updated price value object--------------------******now--------->>>>>>>>>>>>>>>>>>>', priceValues);
    return priceValues;
}


function collectVariableValues() {
    var variableValues = {};
 
    $('#sales-variable tbody tr').each(function() {
        var variableName = $(this).find('td').first().text().trim();
        var variableType = $(this).data('variable-type');
        var variableId = $(this).data('variable-id');
        var value = '';

        switch (variableType) {
            case 'Dates':
                value = $('#variableDatpicker').val();
                break;

            case 'Single Line Text':
                value = $(this).find('.single-line-text').val();
                break;

            case 'Multiple Line Text':
                value = $(this).find('.multiple-line-text').val();
                break;

            case 'Single Box':
                value = $(this).find('input[type="radio"]:checked').val();
                break;

            case 'Multiple Box':
                var multipleBoxValues = [];
                $(this).find('input[type="checkbox"]:checked').each(function() {
                    if ($(this).val() !== "on") {
                        multipleBoxValues.push($(this).val());
                    }
                });
                value = multipleBoxValues.join(',');
                break;
        }

        variableValues[variableName] = {
            type: variableType,
            value: value
        };
    });
    console.log('variableValues****-----------------checking now------------', variableValues);
    return variableValues;
}

function getTheContractmytest(selectedContract) {
    if (selectedContract) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var variableValues = collectVariableValues();
        var priceValues = collectPriceValues();  

        $.ajax({
            url: '/get-pdf-sales',
            type: 'POST',
            data: {
                selectedContractId: selectedContract,
                variableValues: variableValues,
                priceValues: priceValues
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                var pdfUrl = response.pdf_url;

                console.log('pdfUrl----------------->', pdfUrl);
                $('.modal-body').html('<embed src="' + pdfUrl + '" type="application/pdf" style="width:100%; height:100%;">');
                $('#myModal').modal('show');

                $('#closeModalBtn').on('click', function() {
                    $.ajax({
                        url: '/delete-pdf',
                        type: 'POST',
                        data: {
                            pdfUrl: pdfUrl
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            console.log('PDF deleted successfully');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting PDF:', error);
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
}


function handleVariableType(variable, labelValueCell) {
   
    var container, icon, inputField;

    function createIcon() {
        return $('<button type="button" class="btn btn-info btn-sm ml-2"><i class="fas fa-info-circle"></i></button>').click(function() {
            showToast(variable.Description);
        });
    }

    switch (variable.VariableType) {
        case 'Dates':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            var defaultDate = Localdates ? Localdates : new Date().toISOString().slice(0, 10);
            inputField = $('<input type="date" class="form-control flex-grow-1 mr-2" id="variableDatpicker" value="' + defaultDate + '">');
            icon = createIcon();

            container.append(inputField, icon);
            labelValueCell.append(container);

            inputField.on('change', function() {
                updateVariableData(variable.id, $(this).val(), 'Dates');
            });
            break;

        case 'Single Line Text':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            inputField = $('<input class="form-control flex-grow-1 mr-2 single-line-text" type="text">').val(LocalsingleLineText || '');
            icon = createIcon();

            container.append(inputField, icon);
            labelValueCell.append(container);

            inputField.on('input', function() {
                variable.Value = $(this).val();
                updateVariableData(variable.id, $(this).val(), 'Single Line Text');
            });
            break;

        case 'Multiple Line Text':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            inputField = $('<input class="form-control flex-grow-1 mr-2 multiple-line-text" type="text">').val(MultipleLineText || '');
            icon = createIcon();

            container.append(inputField, icon);
            labelValueCell.append(container);

            inputField.on('input', function() {
                variable.Value = $(this).val();
                updateVariableData(variable.id, $(this).val(), 'Multiple Line Text');
            });
            break;

        case 'Single Box':
            container = $('<div class="d-flex flex-column align-items-start w-100 mb-2"></div>');
            icon = createIcon();
            var iconContainer = $('<div class="d-flex align-items-center ml-auto"></div>');

            if (variable.VariableLabelValue && variable.VariableLabelValue.length > 0) {
                var radioGroupName = 'radio_group_' + Math.random().toString(36).substring(7);

                $.each(variable.VariableLabelValue, function(index, value) {
                    var radioContainer = $('<div class="form-check"></div>');
                    var radioBtn = $('<input class="form-check-input" type="radio">').val(value).attr('name', radioGroupName);
                    var radioLabel = $('<label class="form-check-label">' + value + '</label>');

                    if (variable.Value && variable.Value.includes(value)) {
                        radioBtn.prop('checked', true);
                    }

                    radioContainer.append(radioBtn, radioLabel);
                    container.append(radioContainer);

                    radioBtn.on('change', function() {
                        if ($(this).is(':checked')) {
                            console.log('variable.id, $(this).val(), for Single Box -********--------------',variable.id, $(this).val());
                            updateVariableData(variable.id, $(this).val(), 'Single Box');
                        }
                    });
                });
            }
            labelValueCell.append(container);
            iconContainer.append(icon);
            container.append(iconContainer); 
            break;

        case 'Multiple Box':
            container = $('<div class="d-flex flex-column align-items-start w-100 mb-2"></div>');
            icon = createIcon();
            var iconContainer = $('<div class="d-flex align-items-center ml-auto"></div>');

            if (variable.VariableLabelValue && variable.VariableLabelValue.length > 0) {
                $.each(variable.VariableLabelValue, function(index, value) {
                    var checkboxContainer = $('<div class="form-check"></div>');
                    var checkbox = $('<input class="form-check-input" type="checkbox">').val(value);

                    if (variable.Value && variable.Value.includes(value)) {
                        checkbox.prop('checked', true);
                    }

                    var checkboxLabel = $('<label class="form-check-label">' + value + '</label>');

                    checkboxContainer.append(checkbox, checkboxLabel);
                    container.append(checkboxContainer);

                    checkbox.on('change', function() {
                        var selectedValues = [];
                        container.find('input[type="checkbox"]:checked').each(function() {
                            selectedValues.push($(this).val());
                        });
                        console.log('seletedValues----------------*******',selectedValues);
                        updateVariableData(variable.id, selectedValues.join(','), 'Multiple Box');
                    });
                });
            }
            labelValueCell.append(container);
            iconContainer.append(icon);
            container.append(iconContainer); 
            break;
    }
}

function priceTable(selectedContract, id, paymentMinRange, paymentMaxRange, priceJson) {
    var paymentMinRange;
    var paymentMaxRange;
    $.ajax({
    url: '/get-all-priceLists',
    type: 'GET',
    data: {
        selectedContractId: selectedContract
    },
    success: function(response) {
        console.log('get-all-priceLists data:', response);
        priceJsonData = response;

        paymentMinRange = response.paymentMinRange;
        paymentMaxRange = response.paymentMaxRange;

        console.log('paymentMinRange ************-------------nw', paymentMinRange);
        console.log('paymentMaxRange ************-------------nw', paymentMaxRange);

        if (response.dynamicminRange !== null) {
            var newRow = $('<tr>');

            var pricenameCell = $('<td>').html("Price Name: " + response.pricename  +"<br> Min Range: " + response.dynamicminRange + " ,Max range: " +response.dynamicmaxRange);

            

            var currencySymbol;
            switch (response.currency) {
                case 'EUR':
                    currencySymbol = '€';
                    break;
                case 'USD':
                    currencySymbol = '$';
                    break;
                case 'GBP':
                    currencySymbol = '£';
                    break;
                case 'JPY':
                    currencySymbol = '¥';
                    break;
                default:
                    currencySymbol = '';
            }

            var dynamicminRangeInput = $('<input>').attr({
                type: 'text',
                class: 'form-control dynamicminRangeInput',
                value: currencySymbol + ' ' + response.dynamicminRange
            });

            dynamicminRangeInput.on('change', function() {
                var inputValue = parseFloat($(this).val().replace(currencySymbol, '').trim());

                if (inputValue < response.dynamicminRange || inputValue > response.dynamicmaxRange) {
                    alert('The value must be between ' + response.dynamicminRange + ' and ' + response.dynamicmaxRange);
                    $(this).val(currencySymbol + ' ' + response.dynamicminRange);
                } else {
                    priceJsonData.dynamicminRange = inputValue;
                }
            });

            var dynamicminRangeCell = $('<td>').append(dynamicminRangeInput);
            newRow.append(pricenameCell, dynamicminRangeCell);
            $('#sales-variable tbody').append(newRow);
        }

        if (response.fixedvalue !== null) {
            var newRow = $('<tr>');
            var pricenameCell = $('<td>').html("Price Name: " + response.pricename  +"<br> Min Range: " + response.dynamicminRange + " ,Max range: " +response.dynamicmaxRange);

            

            var currencySymbol;
            switch (response.currency) {
                case 'EUR':
                    currencySymbol = '€';
                    break;
                case 'USD':
                    currencySymbol = '$';
                    break;
                case 'GBP':
                    currencySymbol = '£';
                    break;
                case 'JPY':
                    currencySymbol = '¥';
                    break;
                default:
                    currencySymbol = '';
            }

            var fixedvalueInput = $('<input>').attr({
                type: 'text',
                class: 'form-control',
                value: currencySymbol + ' ' + response.fixedvalue,
                readonly: true
            });

            var fixedvalueCell = $('<td>').append(fixedvalueInput);
            newRow.append(pricenameCell, fixedvalueCell);
            $('#sales-variable tbody').append(newRow);
        }

        /************ */
        if (response.multiplePayments === "true") {
    var newRow = $('<tr>');
    var sliderCell = $('<td>').attr('colspan', '2').css('width', '100%');

    var valueLabel = $('<label>').text('Payment Range: ').css('display', 'inline-block').css('margin-right', '10px');
    var valueSpan = $('<span>').attr('id', 'sliderValue').css('display', 'none').css('margin-left', '10px');

    valueLabel.appendTo(sliderCell);
    var sliderContainer = $('<div>').css({
        'display': 'inline-block',
        'width': '70%',
        'vertical-align': 'middle'
    });
    var slider = $('<div>').css('width', '100%');
    slider.appendTo(sliderContainer);
    sliderContainer.appendTo(sliderCell);
    valueSpan.appendTo(sliderCell);
    sliderCell.appendTo(newRow);
    $('#sales-variable tbody').append(newRow);

    noUiSlider.create(slider[0], {
        start: [paymentMaxRange !== undefined ? paymentMaxRange : response.paymentMaxRange],
        connect: [true, false],
        range: {
            'min': paymentMinRange !== undefined ? paymentMinRange : response.paymentMinRange,
            'max': paymentMaxRange !== undefined ? paymentMaxRange : response.paymentMaxRange
        },
        behaviour: 'unconstrained-tap',
        tooltips: {
            to: function(value) {
                return Math.round(value);
            },
            from: function(value) {
                return Math.round(value);
            }
        }
    });

    slider[0].noUiSlider.on('update', function(values, handle) {
        var maxRange = parseInt(values[0]);

        $('#sliderValue').text('(Max value: ' + maxRange + ')').attr('title', 'Current Max Value: ' + maxRange);
        priceJsonData.paymentMaxRange = maxRange;

        // Now we build the table based on the updated slider value
        var selectedFixedValueDiv = document.getElementById("ImpostaTable1");
        selectedFixedValueDiv.innerHTML = '';

        var dynamicminRange = response.dynamicminRange;
        var fixedvalue = response.fixedvalue;
        var currency = response.currency;
        var frequency = response.frequency;
        var EditableDates = response.EditableDates;
        var minRangeSlider = response.dynamicminRange;

        var selectionValue = dynamicminRange != null ? 'dynamic' : 'fixed';
        console.log('selectionValue**********************imp**********-----------------', selectionValue);

        // Declare includeOnPrice and enableVat inside this scope
        var includeOnPrice = response.price === "true";
        console.log('includeOnPrice**********************imp**********-----------------', includeOnPrice);

        var vatPercentage = response.vatPercentage;
        console.log('vatPercentage**********************imp**********-----------------', vatPercentage);
        var enableVat = response.enableVat === "true";
        console.log('enableVat**********************imp**********-----------------', enableVat);

        // Ensure includeOnPrice and enableVat are boolean
        includeOnPrice = Boolean(includeOnPrice);
        enableVat = Boolean(enableVat);

        console.log('Before condition - includeOnPrice:', includeOnPrice, 'enableVat:', enableVat);

        if (selectionValue === 'dynamic') {
            var minRangeSliderCAL = parseFloat(minRangeSlider) + (vatPercentage * parseFloat(minRangeSlider)) / 100;

            if (includeOnPrice && enableVat) {
                console.log('Using minRangeSliderCAL calculation');
                var importo = (minRangeSliderCAL / maxRange).toFixed(2);
            } else {
                console.log('Using minRangeSlider calculation');
                var importo = (minRangeSlider / maxRange).toFixed(2);
            }
        } else {
            if (includeOnPrice && enableVat) {
                console.log('Using newcalculation calculation');
                var importo = (newcalculation / maxRange).toFixed(2);
            } else {
                console.log('Using fixedValueInput calculation');
                var importo = (fixedValueInput / maxRange).toFixed(2);
            }
        }

        var maxRangeVal = response.multiplePayments === "true" ? maxRange : 1;
        var table = document.createElement('table');
        table.className = 'table';

        var thead = table.createTHead();
        var headerRow = thead.insertRow();

        var colName = selectionValue === 'dynamic'
            ? 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + minRangeSlider + ' + IVA sarà corrisposto con le seguenti modalità:'
            : 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + fixedValueInput + ' + IVA sarà corrisposto con le seguenti modalità:';

        var th = document.createElement('th');
        th.textContent = colName;
        th.colSpan = 5;
        headerRow.appendChild(th);

        var tbody = table.createTBody();
        for (var i = 1; i <= maxRangeVal; i++) {
            var row = tbody.insertRow();
            var descrizione = 'Rata ' + i + ' in ' + getCurrencySymbol(currency);

            var importo;
            if (selectionValue === 'dynamic') {
                var minRangeSliderCAL = parseFloat(minRangeSlider) + (vatPercentage * parseFloat(minRangeSlider)) / 100;

                if (includeOnPrice && enableVat) {
                    importo = (minRangeSliderCAL / maxRange).toFixed(2);
                } else {
                    importo = (minRangeSlider / maxRange).toFixed(2);
                }
            } else {
                if (includeOnPrice && enableVat) {
                    importo = (newcalculation / maxRange).toFixed(2);
                } else {
                    importo = (fixedValueInput / maxRange).toFixed(2);
                }
            }

            var importoInput = document.createElement('input');
            importoInput.classList.add('form-control');
            importoInput.type = 'number';
            importoInput.style = "width: 100px;";
            importoInput.value = importo;

            importoInput.addEventListener('input', function() {
                var inputVal = parseFloat(this.value);

                var newImporto;
                if (selectionValue === 'dynamic') {
                    var minRangeSliderCAL = parseFloat(minRangeSlider) + (vatPercentage * parseFloat(minRangeSlider)) / 100;

                    if (includeOnPrice && enableVat) {
                        newImporto = (minRangeSliderCAL - inputVal) / (maxRange - 1);
                    } else {
                        newImporto = (minRangeSlider - inputVal) / (maxRange - 1);
                    }
                } else {
                    if (includeOnPrice && enableVat) {
                        newImporto = (newcalculation - inputVal) / (maxRange - 1);
                    } else {
                        newImporto = (fixedValueInput - inputVal) / (maxRange - 1);
                    }
                }

                var allInputs = tbody.getElementsByTagName('input');
                for (var j = 0; j < allInputs.length; j++) {
                    if (allInputs[j] !== this) {
                        allInputs[j].value = newImporto.toFixed(2);
                    }
                }
            });

            var dovutoIl = getDateByFrequency(frequency, i);

            var dovutoIlInput = document.createElement('input');
            dovutoIlInput.classList.add('form-control');
            dovutoIlInput.style = "width: 128px;";
            dovutoIlInput.type = 'date';

            var parts = dovutoIl.split('/');
            var yyyy_mm_dd = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
            dovutoIlInput.value = yyyy_mm_dd;

            dovutoIlInput.addEventListener('change', function() {
                var rowIndex = Array.from(this.parentNode.parentNode.parentNode.children).indexOf(this.parentNode.parentNode);
                var selectedDate = new Date(this.value);

                for (var k = rowIndex + 1; k < tbody.rows.length; k++) {
                    var nextDate = new Date(selectedDate);
                    switch (frequency) {
                        case 'daily':
                            nextDate.setDate(nextDate.getDate() + 1);
                            break;
                        case 'biweekly':
                            nextDate.setDate(nextDate.getDate() + (14 * 1));
                            break;
                        case 'weekly':
                            nextDate.setDate(nextDate.getDate() + (7 * 1));
                            break;
                        case 'monthly':
                            nextDate.setMonth(nextDate.getMonth() + 1);
                            break;
                        case 'annually':
                            nextDate.setFullYear(nextDate.getFullYear() + 1);
                            break;
                        default:
                            break;
                    }
                    var yyyy_mm_dd = nextDate.toISOString().split('T')[0];

                    tbody.rows[k].cells[4].querySelector('input[type="date"]').value = yyyy_mm_dd;
                    selectedDate = nextDate;
                }
            });

            var calculatedValnew;

            if (enableVat && includeOnPrice) {
                calculatedValnew = "IVA Inc.";
            } else if (!includeOnPrice && enableVat) {
                calculatedValnew = "+ IVA " + vatPercentage + "%";
            } else {
                calculatedValnew = "";
            }

            var cells = EditableDates === 'true'
                ? [descrizione, importoInput, calculatedValnew, 'entro il ', dovutoIlInput]
                : [descrizione, importoInput, calculatedValnew, 'entro il ', dovutoIl];

            cells.forEach(function(cellData) {
                var cell = row.insertCell();
                if (typeof cellData === 'object') {
                    cell.appendChild(cellData);
                } else {
                    cell.textContent = cellData;
                }
            });
        }

            selectedFixedValueDiv.appendChild(table);
        });
 
}

    /*********************** */

    },
    error: function(xhr, status, error) {
        console.error(xhr.responseText);
    }
});
    
}
 

    function showToast(message) {
        var toast = $('#liveToast');
        toast.find('.toast-body').text(message);
        toast.removeClass('hide').addClass('show');
        setTimeout(function() {
            toast.removeClass('show').addClass('hide');
        }, 5000);
    }

    function getDateByFrequency(frequency, offset) {
        var offset = offset - 1;
        var currentDate = new Date();
        switch (frequency) {
            case 'daily':
                currentDate.setDate(currentDate.getDate() + offset);
                break;
            case 'biweekly':
                currentDate.setDate(currentDate.getDate() + (14 * offset));
                break;
            case 'weekly':
                currentDate.setDate(currentDate.getDate() + (7 * offset));
                break;
            case 'monthly':
                currentDate.setMonth(currentDate.getMonth() + offset);
                break;
            case 'annually':
                currentDate.setFullYear(currentDate.getFullYear() + offset);
                break;
            default:
                break;
        }
        var day = currentDate.getDate().toString().padStart(2, '0');
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        var year = currentDate.getFullYear();
        return day + '/' + month + '/' + year;
    }

    function getCurrencySymbol(currencyCode) {
        switch (currencyCode) {
            case 'EUR':
                return '€';
            case 'USD':
                return '$';
            case 'GBP':
                return '£';
            case 'JPY':
                return '¥';
            default:
                return '';
        }
    }
 

    function updateVariableData(key, value, type) {
        if (type === 'Multiple Box') {
            updatedVariableData[key] = value.split(',');
        } else {
            updatedVariableData[key] = value;
        }
    }


    $('#updateButton').on('click', function() {
        update();
    });

    function update() {
        var updatedVariableData = [];

        $('#sales-variable tbody tr').each(function() {
            var row = $(this);
            var variableName = row.find('td').eq(0).text();
            var variableValue = row.find('td').eq(1).find('input, select').val();

            var variableId = row.data('variable-id');
            var variableType = row.data('variable-type');

            var variable = {
                id: variableId,
                name: variableName,
                value: variableValue,
                type: variableType
            };

            updatedVariableData.push(variable);
        });

        var id = window.location.pathname.split('/').pop();

        
        var updateVariableDataPromise = $.ajax({
            url: '/update-variable-data',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                variableData: JSON.stringify(updatedVariableData),
                id: id
            }
        });

        var savePriceJsonDataPromise = $.ajax({
            url: '/save-pricejson-data',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                priceJsonData: JSON.stringify(priceJsonData),
                id: id
            }
        });
        console.log('priceJsonData***------------------------after---user-changes--->',priceJsonData);
    
        $.when(updateVariableDataPromise, savePriceJsonDataPromise).done(function(updateResult, saveResult) {
        
            console.log('Data updated successfully:', updateResult[0].message);
            console.log('Price JSON data saved successfully:', saveResult[0].message);

            Swal.fire({
                title: 'Success',
                text: 'Data updated and price JSON data saved successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }).fail(function(xhr, status, error) {
        
            console.error('Error:', error);
        });
    }

    $.ajax({
        url: '/sales-list-draft/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            LocalvariableJson = response.variable_json;
            processLocalvariableJson();
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });


    function processLocalvariableJson() {
        if (LocalvariableJson) {
            console.log('Processing LocalvariableJson:', LocalvariableJson);
            if (Array.isArray(LocalvariableJson)) {
                LocalvariableJson.forEach(function(item) {
                    console.log('Name:', item.name);
                    console.log('Type:', item.type);
                    console.log('Value:', item.value);
                    console.log('------------------------');

                    switch (item.type) {
                        case 'Single Line Text':
                            LocalsingleLineText = item.value;
                            break;
                        case 'Multiple Line Text':
                            MultipleLineText = item.value;
                            break;
                        case 'Multiple Box':
                            LocalMultipleBox = item.value.split(',');  
                            break;
                        case 'Single Box':
                            LocalSingleBox = item.value;
                            break;
                        case 'Dates':
                            Localdates = item.value;
                            break;
                        default:
                            console.log('Unknown type:', item.type);
                    }
                });
            }
        } else {
            console.log('LocalvariableJson is not defined yet');
        }
    }

 

    $.ajax({
        url: '/get-all-edited-variables',
        type: 'GET',
        data: {
            id: id  
        },
        success: function(response) {
            var selectedContract = response.contractID;
            console.log('contractID  check -----', selectedContract);

            $('#mytestButton').on('click', function() {
                var selectedContract = $('#Contract').val();
                getTheContractmytest(response.contractID);
            });

            var variableData = response.variableData;
            console.log('variableData-------------->', variableData);

            var variableTable = $('#sales-variable');  
            variableTable.find('tbody').empty();  

            
            $.each(variableData, function(index, variable) {
                var tableRow = $('<tr></tr>').attr('data-variable-id', variable.id).attr('data-variable-type', variable.VariableType);
                tableRow.append('<td>' + variable.VariableName + '</td>');
                var labelValueCell = $('<td></td>');
                handleVariableType(variable, labelValueCell);  
                tableRow.append(labelValueCell);
                $('#sales-variable').find('tbody').append(tableRow);
            });

            $.ajax({
                    url: '/get-priceLists-payment',
                    type: 'GET',
                    data: {
                        selectedContractId: selectedContract,
                        id: id
                    },
                    success: function(response) {
                        console.log('price_json:', response.price_json);
                        var priceJson = response.price_json;

                         
                        if (typeof priceJson === 'string') {
                            priceJson = JSON.parse(priceJson);
                        }

                        console.log('Parsed price_json:', priceJson);

             
                        if (priceJson) {
                            paymentMinRange = priceJson.paymentMinRange !== undefined ? priceJson.paymentMinRange : response.paymentMinRange;
                            paymentMaxRange = priceJson.paymentMaxRange !== undefined ? priceJson.paymentMaxRange : response.paymentMaxRange;
                        } else {
                            paymentMinRange = response.paymentMinRange;
                            paymentMaxRange = response.paymentMaxRange;
                        }
                        
                  

                        priceTable(selectedContract, id, paymentMinRange, paymentMaxRange, priceJson);
                        
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
 
        },
        error: function(xhr, status, error) {
            console.error('Error fetching variable JSON data:', error);
        }
    });

    var updatedVariableData = {};
});

</script>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
    #spinner-overlay {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    #spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 120px;
        height: 120px;
    }

    .ring {
        border: 8px solid transparent;
        border-radius: 50%;
        position: absolute;
        animation: spin 1.5s linear infinite;
    }

    .ring:nth-child(1) {
        width: 120px;
        height: 120px;
        border-top: 8px solid #3498db;
        animation-delay: -0.45s;
    }

    .ring:nth-child(2) {
        width: 100px;
        height: 100px;
        border-right: 8px solid #f39c12;
        animation-delay: -0.3s;
    }

    .ring:nth-child(3) {
        width: 80px;
        height: 80px;
        border-bottom: 8px solid #e74c3c;
        animation-delay: -0.15s;
    }

    .ring:nth-child(4) {
        width: 60px;
        height: 60px;
        border-left: 8px solid #9b59b6;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<!-- Spinner Overlay -->
<div id="spinner-overlay">
    <div id="spinner">
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const spinnerOverlay = document.getElementById("spinner-overlay");

        // Show the spinner when the page is loading
        spinnerOverlay.style.display = "block";

        window.addEventListener("load", function() {
            // Hide the spinner when the page has fully loaded
            spinnerOverlay.style.display = "none";
        });

        document.addEventListener("ajaxStart", function() {
            // Show the spinner when an AJAX request starts
            spinnerOverlay.style.display = "block";
        });

        document.addEventListener("ajaxStop", function() {
            // Hide the spinner when the AJAX request completes
            spinnerOverlay.style.display = "none";
        });
    });
</script>


@endsection