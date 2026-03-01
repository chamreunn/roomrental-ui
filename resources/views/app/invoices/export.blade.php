<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - {{ $invoice['invoice_no'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'siemreap', sans-serif;
            font-weight: normal;
            font-size: 13px;
            color: #1a1a2e;
            background: #ffffff;
            line-height: 1.8;
        }

        hr.divider {
            border: none;
            border-top: 2px solid #1a1a2e;
            margin: 10px 0;
        }

        /* Section header row inside a table */
        .section-header {
            background-color: #1a1a2e;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            padding: 5px 8px;
        }

        .label {
            font-weight: bold;
            color: #1a1a2e;
            font-size: 12px;
        }

        .value {
            color: #333;
            font-size: 12px;
        }

        /* Invoice detail table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ccc;
            padding: 6px 9px;
            font-size: 12px;
            font-family: 'siemreap', sans-serif;
            vertical-align: middle;
        }

        .invoice-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #1a1a2e;
            text-align: left;
        }

        .invoice-table td {
            font-weight: normal;
        }

        .text-right {
            text-align: right;
        }

        .total-row th,
        .total-row td {
            background-color: #1a1a2e;
            color: #ffffff;
            font-weight: bold;
            font-size: 13px;
            border-color: #1a1a2e;
        }

        .badge-paid {
            background-color: #27ae60;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
        }

        .badge-unpaid {
            background-color: #c0392b;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
        }

        .notice {
            border-left: 3px solid #c0392b;
            background-color: #fff5f5;
            color: #c0392b;
            font-weight: bold;
            font-size: 12px;
            padding: 6px 10px;
            margin-top: 10px;
            line-height: 1.8;
        }

        .sig-label {
            font-size: 11px;
            color: #555;
            padding-top: 4px;
        }

        .page-num {
            text-align: center;
            font-size: 9px;
            color: #aaa;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    {{-- ========== HEADER ========== --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="30%" valign="middle">
                <span style="font-weight:bold; font-size:16px;">{{ config('app.name', 'Company Name') }}</span><br>
                <span style="font-size:11px; color:#555;">ទូរស័ព្ទ៖ 0987654432</span><br>
                <span style="font-size:11px; color:#555;">ភ្នំពេញ, កម្ពុជា</span>
            </td>
            <td width="40%" align="center" valign="middle">
                <span style="font-weight:bold; font-size:28px; color:#c0392b;">វិក្កយបត្រ</span>
            </td>
            <td width="30%" align="right" valign="middle">
                <span class="label">លេខវិក្កយបត្រ៖</span><br>
                <span class="value">{{ $invoice['invoice_no'] }}</span><br>
                <span class="label">កាលបរិច្ឆេទ៖</span><br>
                <span class="value">{{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('d-M-Y') }}</span><br>
                <span class="label">កាលកំណត់៖</span><br>
                <span class="value">{{ \Carbon\Carbon::parse($invoice['due_date'])->format('d-M-Y') }}</span><br>
                {{-- <br>
                @if (!empty($invoice['status']) && $invoice['status'] === 'paid')
                    <span class="badge-paid">បានបង់</span>
                @else
                    <span class="badge-unpaid">មិនទាន់បង់</span>
                @endif --}}
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ========== ROOM & CLIENT INFO ========== --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            {{-- Room Info --}}
            <td width="50%" valign="top" style="padding-right:12px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="section-header" colspan="2">ព័ត៌មានបន្ទប់</td>
                    </tr>
                    <tr>
                        <td width="45%" class="label" style="padding:3px 0;">បន្ទប់៖</td>
                        <td class="value" style="padding:3px 0;">{{ $invoice['room']['room_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="padding:3px 0;">អគារ៖</td>
                        <td class="value" style="padding:3px 0;">{{ $invoice['room']['building_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="padding:3px 0;">ជាន់៖</td>
                        <td class="value" style="padding:3px 0;">{{ $invoice['room']['floor_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="padding:3px 0;">ប្រភេទបន្ទប់៖</td>
                        <td class="value" style="padding:3px 0;">
                            {{ $invoice['room']['room_type']['type_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="padding:3px 0;">ខែ៖</td>
                        <td class="value" style="padding:3px 0;">
                            {{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('F Y') }}</td>
                    </tr>
                </table>
            </td>

            {{-- Client Info --}}
            <td width="50%" valign="top" style="padding-left:12px;">
                @if (!empty($invoice['room']['clients'][0]))
                    @php $client = $invoice['room']['clients'][0]; @endphp
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td class="section-header" colspan="2">ព័ត៌មានអ្នកជួល</td>
                        </tr>
                        <tr>
                            <td width="45%" class="label" style="padding:3px 0;">ឈ្មោះ៖</td>
                            <td class="value" style="padding:3px 0;">{{ $client['username'] }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="padding:3px 0;">ទូរស័ព្ទ៖</td>
                            <td class="value" style="padding:3px 0;">{{ $client['phone_number'] }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="padding:3px 0;">អ៊ីមែល៖</td>
                            <td class="value" style="padding:3px 0;">{{ $client['email'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="padding:3px 0;">ភេទ៖</td>
                            <td class="value" style="padding:3px 0;">
                                {{ $client['gender'] === 'm' ? 'ប្រុស' : 'ស្រី' }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="padding:3px 0;">អាសយដ្ឋាន៖</td>
                            <td class="value" style="padding:3px 0;">{{ $client['address'] }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="padding:3px 0;">កាលបរិច្ឆេទចូល៖</td>
                            <td class="value" style="padding:3px 0;">
                                {{ \Carbon\Carbon::parse($client['start_rental_date'])->format('d-M-Y') }}</td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ========== INVOICE DETAILS ========== --}}
    <table class="invoice-table">
        <tr>
            <td class="section-header" colspan="4">លម្អិតវិក្កយបត្រ</td>
        </tr>
        <tr>
            <th width="25%">អគ្គិសនីចាស់ (kWh)</th>
            <td width="25%">{{ $invoice['old_electric'] }}</td>
            <th width="25%">អគ្គិសនីថ្មី (kWh)</th>
            <td width="25%">{{ $invoice['new_electric'] }}</td>
        </tr>
        <tr>
            <th>អត្រាអគ្គិសនី</th>
            <td>{{ number_format($invoice['electric_rate'], 2) }} ៛</td>
            <th>សរុបអគ្គិសនី</th>
            <td>{{ number_format($invoice['electric_total'], 2) }} ៛</td>
        </tr>
        <tr>
            <th>ទឹកចាស់ (m³)</th>
            <td>{{ $invoice['old_water'] }}</td>
            <th>ទឹកថ្មី (m³)</th>
            <td>{{ $invoice['new_water'] }}</td>
        </tr>
        <tr>
            <th>អត្រាទឹក</th>
            <td>{{ number_format($invoice['water_rate'], 2) }} ៛</td>
            <th>សរុបទឹក</th>
            <td>{{ number_format($invoice['water_total'], 2) }} ៛</td>
        </tr>
        <tr>
            <th colspan="3" class="text-right">ថ្លៃបន្ទប់</th>
            <td>{{ number_format($invoice['room_fee'], 2) }} ៛</td>
        </tr>
        <tr>
            <th colspan="3" class="text-right">ការចំណាយផ្សេងៗ</th>
            <td>{{ number_format($invoice['other_charge'], 2) }} ៛</td>
        </tr>
        <tr class="total-row">
            <th colspan="3" class="text-right">សរុបទឹកប្រាក់</th>
            <td>{{ number_format($invoice['grand_total'], 2) }} ៛</td>
        </tr>
    </table>

    {{-- ========== NOTICE ========== --}}
    <div class="notice">
        *** អតិថិជនត្រូវបង់ប្រាក់បន្ទប់ អគ្គិសនី និងទឹកអោយបានទាន់ពេលវេលា។ សូមអរគុណ។
    </div>

    <div class="page-num">
        បង្កើតនៅថ្ងៃ {{ now()->format('d-M-Y H:i') }} &mdash; {{ config('app.name') }}
    </div>

</body>

</html>
