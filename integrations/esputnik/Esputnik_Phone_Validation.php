<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Phone_Validation
{
    public static function start_validation(string $phone, string $country_code){
        $validation = new self();
        $rules = $validation->phone_code_rules();
        if(isset($rules[$country_code])) {
            $country_rule = $rules[$country_code];
            if(isset($country_rule['phone_code']) && isset($country_rule['phone_number_length'])){
                return $validation->normalize_number($phone, $country_rule['phone_code'], $country_rule['phone_number_length']);
            }
        } else {
            return null;
        }

    }

    private function normalize_number(string $phoneNumber, string $countryCode, int $expectedLength)
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        $phoneNumberLength = strlen($phoneNumber);
        $countryCodeLength = strlen($countryCode) - 1;
        $missingDigits = $expectedLength - $phoneNumberLength;

        if ($missingDigits > 0 && $missingDigits <= $countryCodeLength) {
            $phoneNumber = substr($phoneNumber, $countryCodeLength - $missingDigits);
            return $countryCode . $phoneNumber;
        } else if ($missingDigits === 0) return $phoneNumber;
        else return null;
    }

    private function phone_code_rules(){
        return array(
            //Europe
            "UA" => array(
                "phone_code" => "+380",
                "wordpress_code" => "UA",
                "phone_number_length" => 12
            ),
            "AT" => array(
                "phone_code" => "+43",
                "wordpress_code" => "AT",
                "phone_number_length" => 11
            ),
            "AL" => array(
                "phone_code" => "+355",
                "wordpress_code" => "AL",
                "phone_number_length" => 11
            ),
            "AD" => array(
                "phone_code" => "+376",
                "wordpress_code" => "AD",
                "phone_number_length" => 9
            ),
            "BE" => array(
                "phone_code" => "+32",
                "wordpress_code" => "BE",
                "phone_number_length" => 10
            ),
            "BY" => array(
                "phone_code" => "+375",
                "wordpress_code" => "BY",
                "phone_number_length" => 12
            ),
            "BG" => array(
                "phone_code" => "+359",
                "wordpress_code" => "BG",
                "phone_number_length" => 11
            ),
            "BA" => array(
                "phone_code" => "+387",
                "wordpress_code" => "BA",
                "phone_number_length" => 11
            ),
            "VA" => array(
                "phone_code" => "+379",
                "wordpress_code" => "VA",
                "phone_number_length" => 13
            ),
            "GB" => array(
                "phone_code" => "+44",
                "wordpress_code" => "GB",
                "phone_number_length" => 12
            ),
            "GR" => array(
                "phone_code" => "+30",
                "wordpress_code" => "GR",
                "phone_number_length" => 12
            ),
            "DK" => array(
                "phone_code" => "+45",
                "wordpress_code" => "DK",
                "phone_number_length" => 10
            ),
            "EE" => array(
                "phone_code" => "+372",
                "wordpress_code" => "EE",
                "phone_number_length" => 10
            ),
            "IE" => array(
                "phone_code" => "+353",
                "wordpress_code" => "IE",
                "phone_number_length" => 12
            ),
            "IS" => array(
                "phone_code" => "+354",
                "wordpress_code" => "IS",
                "phone_number_length" => 10
            ),
            "ES" => array(
                "phone_code" => "+34",
                "wordpress_code" => "ES",
                "phone_number_length" => 11
            ),
            "IT" => array(
                "phone_code" => "+39",
                "wordpress_code" => "IT",
                "phone_number_length" => 12
            ),
            "XK" => array(
                "phone_code" => "+383",
                "wordpress_code" => "XK",
                "phone_number_length" => 11
            ),
            "LV" => array(
                "phone_code" => "+371",
                "wordpress_code" => "LV",
                "phone_number_length" => 11
            ),
            "LT" => array(
                "phone_code" => "+370",
                "wordpress_code" => "LT",
                "phone_number_length" => 11
            ),
            "LI" => array(
                "phone_code" => "+423",
                "wordpress_code" => "LI",
                "phone_number_length" => 10
            ),
            "LU" => array(
                "phone_code" => "+352",
                "wordpress_code" => "LU",
                "phone_number_length" => 11
            ),
            "MT" => array(
                "phone_code" => "+356",
                "wordpress_code" => "MT",
                "phone_number_length" => 11
            ),
            "MD" => array(
                "phone_code" => "+373",
                "wordpress_code" => "MD",
                "phone_number_length" => 11
            ),
            "MC" => array(
                "phone_code" => "+377",
                "wordpress_code" => "MC",
                "phone_number_length" => 11
            ),
            "NL" => array(
                "phone_code" => "+31",
                "wordpress_code" => "NL",
                "phone_number_length" => 11
            ),
            "DE" => array(
                "phone_code" => "+49",
                "wordpress_code" => "DE",
                "phone_number_length" => 11
            ),
            "NO" => array(
                "phone_code" => "+47",
                "wordpress_code" => "NO",
                "phone_number_length" => 10
            ),
            "MK" => array(
                "phone_code" => "+389",
                "wordpress_code" => "MK",
                "phone_number_length" => 11
            ),
            "PL" => array(
                "phone_code" => "+48",
                "wordpress_code" => "PL",
                "phone_number_length" => 11
            ),
            "PT" => array(
                "phone_code" => "+351",
                "wordpress_code" => "PT",
                "phone_number_length" => 12
            ),
            "RU" => array(
                "phone_code" => "+7",
                "wordpress_code" => "RU",
                "phone_number_length" => 11
            ),
            "RO" => array(
                "phone_code" => "+40",
                "wordpress_code" => "RO",
                "phone_number_length" => 11
            ),
            "SM" => array(
                "phone_code" => "+378",
                "wordpress_code" => "SM",
                "phone_number_length" => 13
            ),
            "RS" => array(
                "phone_code" => "+381",
                "wordpress_code" => "RS",
                "phone_number_length" => 12
            ),
            "SK" => array(
                "phone_code" => "+421",
                "wordpress_code" => "SK",
                "phone_number_length" => 12
            ),
            "SI" => array(
                "phone_code" => "+386",
                "wordpress_code" => "SI",
                "phone_number_length" => 11
            ),
            "HU" => array(
                "phone_code" => "+36",
                "wordpress_code" => "HU",
                "phone_number_length" => 11
            ),
            "FI" => array(
                "phone_code" => "+358",
                "wordpress_code" => "FI",
                "phone_number_length" => 11
            ),
            "FR" => array(
                "phone_code" => "+33",
                "wordpress_code" => "FR",
                "phone_number_length" => 11
            ),
            "HR" => array(
                "phone_code" => "+385",
                "wordpress_code" => "HR",
                "phone_number_length" => 11
            ),
            "CZ" => array(
                "phone_code" => "+420",
                "wordpress_code" => "CZ",
                "phone_number_length" => 12
            ),
            "ME" => array(
                "phone_code" => "+382",
                "wordpress_code" => "ME",
                "phone_number_length" => 11
            ),
            "CH" => array(
                "phone_code" => "+41",
                "wordpress_code" => "CH",
                "phone_number_length" => 11
            ),
            "SE" => array(
                "phone_code" => "+46",
                "wordpress_code" => "SE",
                "phone_number_length" => 11
            ),

            "GE" => array(
                "phone_code" => "+995",
                "wordpress_code" => "GE",
                "phone_number_length" => 12
            ),
            "AM" => array(
                "phone_code" => "+374",
                "wordpress_code" => "AM",
                "phone_number_length" => 11
            ),
            "AZ" => array(
                "phone_code" => "+994",
                "wordpress_code" => "AZ",
                "phone_number_length" => 12
            ),
            "TR" => array(
                "phone_code" => "+90",
                "wordpress_code" => "TR",
                "phone_number_length" => 12
            ),
            "KZ" => array(
                "phone_code" => "+7",
                "wordpress_code" => "KZ",
                "phone_number_length" => 11
            ),

            //NORTHERN AMERICA
            "AG" => array(
                "phone_code" => "+1-268",
                "wordpress_code" => "AG",
                "phone_number_length" => 11
            ),
            "BS" => array(
                "phone_code" => "+1-242",
                "wordpress_code" => "BS",
                "phone_number_length" => 11
            ),
            "BB" => array(
                "phone_code" => "+1-246",
                "wordpress_code" => "BB",
                "phone_number_length" => 11
            ),
            "BZ" => array(
                "phone_code" => "+501",
                "wordpress_code" => "BZ",
                "phone_number_length" => 10
            ),
            "HT" => array(
                "phone_code" => "+509",
                "wordpress_code" => "HT",
                "phone_number_length" => 11
            ),
            "GT" => array(
                "phone_code" => "+502",
                "wordpress_code" => "GT",
                "phone_number_length" => 11
            ),
            "HN" => array(
                "phone_code" => "+504",
                "wordpress_code" => "HN",
                "phone_number_length" => 11
            ),
            "GD" => array(
                "phone_code" => "+1-473",
                "wordpress_code" => "GD",
                "phone_number_length" => 11
            ),
            "DM" => array(
                "phone_code" => "+1-767",
                "wordpress_code" => "DM",
                "phone_number_length" => 11
            ),
            "DO" => array(
                "phone_code" => "+1-809",
                "wordpress_code" => "DO",
                "phone_number_length" => 11
            ),
            "CA" => array(
                "phone_code" => "+1",
                "wordpress_code" => "CA",
                "phone_number_length" => 11
            ),
            "CR" => array(
                "phone_code" => "+506",
                "wordpress_code" => "CR",
                "phone_number_length" => 11
            ),
            "CU" => array(
                "phone_code" => "+53",
                "wordpress_code" => "CU",
                "phone_number_length" => 10
            ),
            "MX" => array(
                "phone_code" => "+52",
                "wordpress_code" => "MX",
                "phone_number_length" => 12
            ),
            "NI" => array(
                "phone_code" => "+505",
                "wordpress_code" => "NI",
                "phone_number_length" => 11
            ),
            "PA" => array(
                "phone_code" => "+507",
                "wordpress_code" => "PA",
                "phone_number_length" => 11
            ),
            "SV" => array(
                "phone_code" => "+503",
                "wordpress_code" => "SV",
                "phone_number_length" => 11
            ),
            "VC" => array(
                "phone_code" => "+1-784",
                "wordpress_code" => "VC",
                "phone_number_length" => 11
            ),
            "KN" => array(
                "phone_code" => "+1-869",
                "wordpress_code" => "KN",
                "phone_number_length" => 11
            ),
            "LC" => array(
                "phone_code" => "+1-758",
                "wordpress_code" => "LC",
                "phone_number_length" => 11
            ),
            "US" => array(
                "phone_code" => "+1",
                "wordpress_code" => "US",
                "phone_number_length" => 11
            ),
            "TT" => array(
                "phone_code" => "+1-868",
                "wordpress_code" => "TT",
                "phone_number_length" => 11
            ),
            "JM" => array(
                "phone_code" => "+1-876",
                "wordpress_code" => "JM",
                "phone_number_length" => 11
            ),
            //SOUTHERN AMERICA
            "AR" => array(
                "phone_code" => "+54",
                "wordpress_code" => "AR",
                "phone_number_length" => 12
            ),
            "BO" => array(
                "phone_code" => "+591",
                "wordpress_code" => "BO",
                "phone_number_length" => 11
            ),
            "BR" => array(
                "phone_code" => "+55",
                "wordpress_code" => "BR",
                "phone_number_length" => 12
            ),
            "VE" => array(
                "phone_code" => "+58",
                "wordpress_code" => "VE",
                "phone_number_length" => 12
            ),
            "GY" => array(
                "phone_code" => "+592",
                "wordpress_code" => "GY",
                "phone_number_length" => 10
            ),
            "EC" => array(
                "phone_code" => "+593",
                "wordpress_code" => "EC",
                "phone_number_length" => 12
            ),
            "CO" => array(
                "phone_code" => "+57",
                "wordpress_code" => "CO",
                "phone_number_length" => 12
            ),
            "PY" => array(
                "phone_code" => "+595",
                "wordpress_code" => "PY",
                "phone_number_length" => 11
            ),
            "PE" => array(
                "phone_code" => "+51",
                "wordpress_code" => "PE",
                "phone_number_length" => 10
            ),
            "SR" => array(
                "phone_code" => "+597",
                "wordpress_code" => "SR",
                "phone_number_length" => 9
            ),
            "UY" => array(
                "phone_code" => "+598",
                "wordpress_code" => "UY",
                "phone_number_length" => 11
            ),
            "CL" => array(
                "phone_code" => "+56",
                "wordpress_code" => "CL",
                "phone_number_length" => 11
            ),

            //ASIA
            "AF" => array(
                "phone_code" => "+93",
                "wordpress_code" => "AF",
                "phone_number_length" => 11
            ),
            "BD" => array(
                "phone_code" => "+880",
                "wordpress_code" => "BD",
                "phone_number_length" => 13
            ),
            "BH" => array(
                "phone_code" => "+973",
                "wordpress_code" => "BH",
                "phone_number_length" => 11
            ),
            "BN" => array(
                "phone_code" => "+673",
                "wordpress_code" => "BN",
                "phone_number_length" => 10
            ),
            "BT" => array(
                "phone_code" => "+975",
                "wordpress_code" => "BT",
                "phone_number_length" => 10
            ),
            "VN" => array(
                "phone_code" => "+84",
                "wordpress_code" => "VN",
                "phone_number_length" => 12
            ),
            "YE" => array(
                "phone_code" => "+967",
                "wordpress_code" => "YE",
                "phone_number_length" => 10
            ),
            "IL" => array(
                "phone_code" => "+972",
                "wordpress_code" => "IL",
                "phone_number_length" => 11
            ),
            "IN" => array(
                "phone_code" => "+91",
                "wordpress_code" => "IN",
                "phone_number_length" => 12
            ),
            "ID" => array(
                "phone_code" => "+62",
                "wordpress_code" => "ID",
                "phone_number_length" => 13
            ),
            "IQ" => array(
                "phone_code" => "+964",
                "wordpress_code" => "IQ",
                "phone_number_length" => 13
            ),
            "IR" => array(
                "phone_code" => "+98",
                "wordpress_code" => "IR",
                "phone_number_length" => 12
            ),
            "JO" => array(
                "phone_code" => "+962",
                "wordpress_code" => "JO",
                "phone_number_length" => 12
            ),
            "KH" => array(
                "phone_code" => "+855",
                "wordpress_code" => "KH",
                "phone_number_length" => 11
            ),
            "KG" => array(
                "phone_code" => "+996",
                "wordpress_code" => "KG",
                "phone_number_length" => 12
            ),
            "CN" => array(
                "phone_code" => "+86",
                "wordpress_code" => "CN",
                "phone_number_length" => 12
            ),
            "CY" => array(
                "phone_code" => "+357",
                "wordpress_code" => "CY",
                "phone_number_length" => 11
            ),
            "KW" => array(
                "phone_code" => "+965",
                "wordpress_code" => "KW",
                "phone_number_length" => 10
            ),
            "LA" => array(
                "phone_code" => "+856",
                "wordpress_code" => "LA",
                "phone_number_length" => 11
            ),
            "LB" => array(
                "phone_code" => "+961",
                "wordpress_code" => "LB",
                "phone_number_length" => 10
            ),
            "MY" => array(
                "phone_code" => "+60",
                "wordpress_code" => "MY",
                "phone_number_length" => 10
            ),
            "MV" => array(
                "phone_code" => "+960",
                "wordpress_code" => "MV",
                "phone_number_length" => 10
            ),
            "MN" => array(
                "phone_code" => "+976",
                "wordpress_code" => "MN",
                "phone_number_length" => 11
            ),
            "MM" => array(
                "phone_code" => "+95",
                "wordpress_code" => "MM",
                "phone_number_length" => 12
            ),
            "NP" => array(
                "phone_code" => "+977",
                "wordpress_code" => "NP",
                "phone_number_length" => 13
            ),
            "AE" => array(
                "phone_code" => "+971",
                "wordpress_code" => "AE",
                "phone_number_length" => 11
            ),
            "OM" => array(
                "phone_code" => "+968",
                "wordpress_code" => "OM",
                "phone_number_length" => 11
            ),
            "PK" => array(
                "phone_code" => "+92",
                "wordpress_code" => "PK",
                "phone_number_length" => 13
            ),
            "PS" => array(
                "phone_code" => "+970",
                "wordpress_code" => "PS",
                "phone_number_length" => 12
            ),
            "KR" => array(
                "phone_code" => "+82",
                "wordpress_code" => "KR",
                "phone_number_length" => 10
            ),
            "QA" => array(
                "phone_code" => "+974",
                "wordpress_code" => "QA",
                "phone_number_length" => 11
            ),
            "KP" => array(
                "phone_code" => "+850",
                "wordpress_code" => "KP",
                "phone_number_length" => 12
            ),
            "SA" => array(
                "phone_code" => "+966",
                "wordpress_code" => "SA",
                "phone_number_length" => 12
            ),
            "SY" => array(
                "phone_code" => "+963",
                "wordpress_code" => "SY",
                "phone_number_length" => 12
            ),
            "SG" => array(
                "phone_code" => "+65",
                "wordpress_code" => "SG",
                "phone_number_length" => 10
            ),
            "TL" => array(
                "phone_code" => "+670",
                "wordpress_code" => "TL",
                "phone_number_length" => 11
            ),
            "TJ" => array(
                "phone_code" => "+992",
                "wordpress_code" => "TJ",
                "phone_number_length" => 12
            ),
            "TH" => array(
                "phone_code" => "+66",
                "wordpress_code" => "TH",
                "phone_number_length" => 10
            ),
            "TW" => array(
                "phone_code" => "+886",
                "wordpress_code" => "TW",
                "phone_number_length" => 11
            ),
            "TM" => array(
                "phone_code" => "+993",
                "wordpress_code" => "TM",
                "phone_number_length" => 11
            ),
            "UZ" => array(
                "phone_code" => "+998",
                "wordpress_code" => "UZ",
                "phone_number_length" => 12
            ),
            "PH" => array(
                "phone_code" => "+63",
                "wordpress_code" => "PH",
                "phone_number_length" => 12
            ),
            "LK" => array(
                "phone_code" => "+94",
                "wordpress_code" => "LK",
                "phone_number_length" => 11
            ),
            "JP" => array(
                "phone_code" => "+81",
                "wordpress_code" => "JP",
                "phone_number_length" => 11
            ),

            //OCEAN REGION
            "AU" => array(
                "phone_code" => "+61",
                "wordpress_code" => "AU",
                "phone_number_length" => 11
            ),
            "VU" => array(
                "phone_code" => "+678",
                "wordpress_code" => "VU",
                "phone_number_length" => 8
            ),
            "KI" => array(
                "phone_code" => "+686",
                "wordpress_code" => "KI",
                "phone_number_length" => 11
            ),
            "MH" => array(
                "phone_code" => "+692",
                "wordpress_code" => "MH",
                "phone_number_length" => 10
            ),
            "NR" => array(
                "phone_code" => "+674",
                "wordpress_code" => "NR",
                "phone_number_length" => 10
            ),
            "NZ" => array(
                "phone_code" => "+64",
                "wordpress_code" => "NZ",
                "phone_number_length" => 10
            ),
            "PW" => array(
                "phone_code" => "+680",
                "wordpress_code" => "PW",
                "phone_number_length" => 10
            ),
            "PG" => array(
                "phone_code" => "+675",
                "wordpress_code" => "PG",
                "phone_number_length" => 10
            ),
            "WS" => array(
                "phone_code" => "+685",
                "wordpress_code" => "WS",
                "phone_number_length" => 8
            ),
            "SB" => array(
                "phone_code" => "+677",
                "wordpress_code" => "SB",
                "phone_number_length" => 8
            ),
            "TO" => array(
                "phone_code" => "+676",
                "wordpress_code" => "TO",
                "phone_number_length" => 10
            ),
            "TV" => array(
                "phone_code" => "+688",
                "wordpress_code" => "TV",
                "phone_number_length" => 10
            ),
            "FM" => array(
                "phone_code" => "+691",
                "wordpress_code" => "FM",
                "phone_number_length" => 10
            ),
            "FJ" => array(
                "phone_code" => "+679",
                "wordpress_code" => "FJ",
                "phone_number_length" => 10
            ),

            //AFRICA
            "DZ" => array(
                "phone_code" => "+213",
                "wordpress_code" => "DZ",
                "phone_number_length" => 11
            ),
            "AO" => array(
                "phone_code" => "+244",
                "wordpress_code" => "AO",
                "phone_number_length" => 12
            ),
            "BJ" => array(
                "phone_code" => "+229",
                "wordpress_code" => "BJ",
                "phone_number_length" => 11
            ),
            "BW" => array(
                "phone_code" => "+267",
                "wordpress_code" => "BW",
                "phone_number_length" => 10
            ),
            "BF" => array(
                "phone_code" => "+226",
                "wordpress_code" => "BF",
                "phone_number_length" => 11
            ),
            "BI" => array(
                "phone_code" => "+257",
                "wordpress_code" => "BI",
                "phone_number_length" => 11
            ),
            "GA" => array(
                "phone_code" => "+241",
                "wordpress_code" => "GA",
                "phone_number_length" => 11
            ),
            "GM" => array(
                "phone_code" => "+220",
                "wordpress_code" => "GM",
                "phone_number_length" => 10
            ),
            "GH" => array(
                "phone_code" => "+233",
                "wordpress_code" => "GH",
                "phone_number_length" => 12
            ),
            "GN" => array(
                "phone_code" => "+224",
                "wordpress_code" => "GN",
                "phone_number_length" => 12
            ),
            "GW" => array(
                "phone_code" => "+245",
                "wordpress_code" => "GW",
                "phone_number_length" => 12
            ),
            "CD" => array(
                "phone_code" => "+243",
                "wordpress_code" => "CD",
                "phone_number_length" => 12
            ),
            "DJ" => array(
                "phone_code" => "+253",
                "wordpress_code" => "DJ",
                "phone_number_length" => 11
            ),
            "GQ" => array(
                "phone_code" => "+240",
                "wordpress_code" => "GQ",
                "phone_number_length" => 12
            ),
            "ER" => array(
                "phone_code" => "+291",
                "wordpress_code" => "ER",
                "phone_number_length" => 10
            ),
            "SZ" => array(
                "phone_code" => "+268",
                "wordpress_code" => "SZ",
                "phone_number_length" => 11
            ),

            "ET" => array(
                "phone_code" => "+251",
                "wordpress_code" => "ET",
                "phone_number_length" => 12
            ),
            "EG" => array(
                "phone_code" => "+20",
                "wordpress_code" => "EG",
                "phone_number_length" => 11
            ),
            "ZM" => array(
                "phone_code" => "+260",
                "wordpress_code" => "ZM",
                "phone_number_length" => 12
            ),
            "EH" => array(
                "phone_code" => "+212",
                "wordpress_code" => "EH",
                "phone_number_length" => 12
            ),
            "ZW" => array(
                "phone_code" => "+263",
                "wordpress_code" => "ZW",
                "phone_number_length" => 13
            ),
            "CV" => array(
                "phone_code" => "+238",
                "wordpress_code" => "CV",
                "phone_number_length" => 10
            ),
            "CM" => array(
                "phone_code" => "+237",
                "wordpress_code" => "CM",
                "phone_number_length" => 12
            ),
            "KE" => array(
                "phone_code" => "+254",
                "wordpress_code" => "KE",
                "phone_number_length" => 12
            ),
            "KM" => array(
                "phone_code" => "+269",
                "wordpress_code" => "KM",
                "phone_number_length" => 10
            ),
            "CI" => array(
                "phone_code" => "+225",
                "wordpress_code" => "CI",
                "phone_number_length" => 13
            ),
            "LS" => array(
                "phone_code" => "+266",
                "wordpress_code" => "LS",
                "phone_number_length" => 11
            ),
            "LR" => array(
                "phone_code" => "+231",
                "wordpress_code" => "LR",
                "phone_number_length" => 12
            ),
            "LY" => array(
                "phone_code" => "+218",
                "wordpress_code" => "LY",
                "phone_number_length" => 12
            ),
            "MU" => array(
                "phone_code" => "+230",
                "wordpress_code" => "MU",
                "phone_number_length" => 10
            ),
            "MR" => array(
                "phone_code" => "+222",
                "wordpress_code" => "MR",
                "phone_number_length" => 11
            ),
            "MG" => array(
                "phone_code" => "+261",
                "wordpress_code" => "MG",
                "phone_number_length" => 12
            ),
            "MW" => array(
                "phone_code" => "+265",
                "wordpress_code" => "MW",
                "phone_number_length" => 12
            ),

            "ML" => array(
                "phone_code" => "+223",
                "wordpress_code" => "ML",
                "phone_number_length" => 11
            ),
            "MA" => array(
                "phone_code" => "+212",
                "wordpress_code" => "MA",
                "phone_number_length" => 12
            ),
            "MZ" => array(
                "phone_code" => "+258",
                "wordpress_code" => "MZ",
                "phone_number_length" => 12
            ),
            "NA" => array(
                "phone_code" => "+264",
                "wordpress_code" => "NA",
                "phone_number_length" => 12
            ),
            "NE" => array(
                "phone_code" => "+227",
                "wordpress_code" => "NE",
                "phone_number_length" => 11
            ),
            "NG" => array(
                "phone_code" => "+234",
                "wordpress_code" => "NG",
                "phone_number_length" => 13
            ),
            "SS" => array(
                "phone_code" => "+211",
                "wordpress_code" => "SS",
                "phone_number_length" => 12
            ),
            "ZA" => array(
                "phone_code" => "+27",
                "wordpress_code" => "ZA",
                "phone_number_length" => 11
            ),
            "CG" => array(
                "phone_code" => "+242",
                "wordpress_code" => "CG",
                "phone_number_length" => 12
            ),
            "RW" => array(
                "phone_code" => "+250",
                "wordpress_code" => "RW",
                "phone_number_length" => 12
            ),
            "ST" => array(
                "phone_code" => "+239",
                "wordpress_code" => "ST",
                "phone_number_length" => 10
            ),
            "SC" => array(
                "phone_code" => "+248",
                "wordpress_code" => "SC",
                "phone_number_length" => 10
            ),

            "SN" => array(
                "phone_code" => "+221",
                "wordpress_code" => "SN",
                "phone_number_length" => 12
            ),
            "SO" => array(
                "phone_code" => "+252",
                "wordpress_code" => "SO",
                "phone_number_length" => 12
            ),
            "SD" => array(
                "phone_code" => "+249",
                "wordpress_code" => "SD",
                "phone_number_length" => 12
            ),
            "SL" => array(
                "phone_code" => "+232",
                "wordpress_code" => "SL",
                "phone_number_length" => 11
            ),
            "TZ" => array(
                "phone_code" => "+255",
                "wordpress_code" => "TZ",
                "phone_number_length" => 12
            ),
            "TG" => array(
                "phone_code" => "+228",
                "wordpress_code" => "TG",
                "phone_number_length" => 11
            ),
            "TN" => array(
                "phone_code" => "+216",
                "wordpress_code" => "TN",
                "phone_number_length" => 11
            ),
            "UG" => array(
                "phone_code" => "+256",
                "wordpress_code" => "UG",
                "phone_number_length" => 12
            ),
            "CF" => array(
                "phone_code" => "+236",
                "wordpress_code" => "CF",
                "phone_number_length" => 11
            ),
            "TD" => array(
                "phone_code" => "+235",
                "wordpress_code" => "TD",
                "phone_number_length" => 11
            ),

        );
    }
}