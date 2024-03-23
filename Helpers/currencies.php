<?php
function vh_get_country_by_country_currencies($country_code)
{
    $currencies = vh_get_country_currencies();

    $country = vh_search_currencies($currencies, 'code', $country_code);

    return $country;
}
//---------------------------------------------------

function vh_get_country_by_country_currency_name($country_name)
{
    $currencies = vh_get_country_currencies();

    return vh_search_currencies($currencies, 'name', $country_name);
}

//---------------------------------------------------

function vh_get_country_by_currency($currency)
{
    $currencies = vh_get_country_currencies();

    return vh_search_currencies($currencies, 'currencies', $currency);
}

//---------------------------------------------------
//---------------------------------------------------
//---------------------------------------------------

function vh_search_currencies($array, $key_name, $value)
{
    foreach($array as $key => $array_item)
    {
        if ( $array_item[$key_name] === $value )
            return $array[$key];
    }

    return false;
}

//---------------------------------------------------

function vh_get_country_list_select_options_on_currencies($show = 'country_name')
{
    $html = "";
    $list = vh_get_country_currencies();

    $html .= '<option value="">Select</option>';

    foreach ($list as $item)
    {
        if($show == 'country_name')
        {
            $html .= '<option value="'.$item['code'].'">'.$item['name'].'</option>';
        } else if($show == 'country_code')
        {
            $html .= '<option value="'.$item['code'].'">'.$item['code'].'</option>';
        } else if($show == 'currency')
        {
            $html .= '<option value="'.$item['currencies'].'">'.$item['currencies'].'</option>';
        }
    }

    return $html;
}

//---------------------------------------------------

function vh_get_country_currencies()
{

    $currencies = [
        ["name" => "Afghan Afghani", "code" => "AFA", "symbol" => "؋"],
        ["name" => "Albanian Lek", "code" => "ALL", "symbol" => "Lek"],
        ["name" => "Algerian Dinar", "code" => "DZD", "symbol" => "دج"],
        ["name" => "Angolan Kwanza", "code" => "AOA", "symbol" => "Kz"],
        ["name" => "Argentine Peso", "code" => "ARS", "symbol" => "$"],
        ["name" => "Armenian Dram", "code" => "AMD", "symbol" => "֏"],
        ["name" => "Aruban Florin", "code" => "AWG", "symbol" => "ƒ"],
        ["name" => "Australian Dollar", "code" => "AUD", "symbol" => "$"],
        ["name" => "Azerbaijani Manat", "code" => "AZN", "symbol" => "m"],
        ["name" => "Bahamian Dollar", "code" => "BSD", "symbol" => "B$"],
        ["name" => "Bahraini Dinar", "code" => "BHD", "symbol" => ".د.ب"],
        ["name" => "Bangladeshi Taka", "code" => "BDT", "symbol" => "৳"],
        ["name" => "Barbadian Dollar", "code" => "BBD", "symbol" => "Bds$"],
        ["name" => "Belarusian Ruble", "code" => "BYR", "symbol" => "Br"],
        ["name" => "Belgian Franc", "code" => "BEF", "symbol" => "fr"],
        ["name" => "Belize Dollar", "code" => "BZD", "symbol" => "$"],
        ["name" => "Bermudan Dollar", "code" => "BMD", "symbol" => "$"],
        ["name" => "Bhutanese Ngultrum", "code" => "BTN", "symbol" => "Nu."],
        ["name" => "Bitcoin", "code" => "BTC", "symbol" => "฿"],
        ["name" => "Bolivian Boliviano", "code" => "BOB", "symbol" => "Bs."],
        ["name" => "Bosnia-Herzegovina Convertible Mark", "code" => "BAM", "symbol" => "KM"],
        ["name" => "Botswanan Pula", "code" => "BWP", "symbol" => "P"],
        ["name" => "Brazilian Real", "code" => "BRL", "symbol" => "R$"],
        ["name" => "British Pound Sterling", "code" => "GBP", "symbol" => "£"],
        ["name" => "Brunei Dollar", "code" => "BND", "symbol" => "B$"],
        ["name" => "Bulgarian Lev", "code" => "BGN", "symbol" => "Лв."],
        ["name" => "Burundian Franc", "code" => "BIF", "symbol" => "FBu"],
        ["name" => "Cambodian Riel", "code" => "KHR", "symbol" => "KHR"],
        ["name" => "Canadian Dollar", "code" => "CAD", "symbol" => "$"],
        ["name" => "Cape Verdean Escudo", "code" => "CVE", "symbol" => "$"],
        ["name" => "Cayman Islands Dollar", "code" => "KYD", "symbol" => "$"],
        ["name" => "CFA Franc BCEAO", "code" => "XOF", "symbol" => "CFA"],
        ["name" => "CFA Franc BEAC", "code" => "XAF", "symbol" => "FCFA"],
        ["name" => "CFP Franc", "code" => "XPF", "symbol" => "₣"],
        ["name" => "Chilean Peso", "code" => "CLP", "symbol" => "$"],
        ["name" => "Chilean Unit of Account", "code" => "CLF", "symbol" => "CLF"],
        ["name" => "Chinese Yuan", "code" => "CNY", "symbol" => "¥"],
        ["name" => "Colombian Peso", "code" => "COP", "symbol" => "$"],
        ["name" => "Comorian Franc", "code" => "KMF", "symbol" => "CF"],
        ["name" => "Congolese Franc", "code" => "CDF", "symbol" => "FC"],
        ["name" => "Costa Rican Colón", "code" => "CRC", "symbol" => "₡"],
        ["name" => "Croatian Kuna", "code" => "HRK", "symbol" => "kn"],
        ["name" => "Cuban Convertible Peso", "code" => "CUC", "symbol" => "$, CUC"],
        ["name" => "Czech Republic Koruna", "code" => "CZK", "symbol" => "Kč"],
        ["name" => "Danish Krone", "code" => "DKK", "symbol" => "Kr."],
        ["name" => "Djiboutian Franc", "code" => "DJF", "symbol" => "Fdj"],
        ["name" => "Dominican Peso", "code" => "DOP", "symbol" => "$"],
        ["name" => "East Caribbean Dollar", "code" => "XCD", "symbol" => "$"],
        ["name" => "Egyptian Pound", "code" => "EGP", "symbol" => "ج.م"],
        ["name" => "Eritrean Nakfa", "code" => "ERN", "symbol" => "Nfk"],
        ["name" => "Estonian Kroon", "code" => "EEK", "symbol" => "kr"],
        ["name" => "Ethiopian Birr", "code" => "ETB", "symbol" => "Nkf"],
        ["name" => "Euro", "code" => "EUR", "symbol" => "€"],
        ["name" => "Falkland Islands Pound", "code" => "FKP", "symbol" => "£"],
        ["name" => "Fijian Dollar", "code" => "FJD", "symbol" => "FJ$"],
        ["name" => "Gambian Dalasi", "code" => "GMD", "symbol" => "D"],
        ["name" => "Georgian Lari", "code" => "GEL", "symbol" => "ლ"],
        ["name" => "German Mark", "code" => "DEM", "symbol" => "DM"],
        ["name" => "Ghanaian Cedi", "code" => "GHS", "symbol" => "GH₵"],
        ["name" => "Gibraltar Pound", "code" => "GIP", "symbol" => "£"],
        ["name" => "Greek Drachma", "code" => "GRD", "symbol" => "₯, Δρχ, Δρ"],
        ["name" => "Guatemalan Quetzal", "code" => "GTQ", "symbol" => "Q"],
        ["name" => "Guinean Franc", "code" => "GNF", "symbol" => "FG"],
        ["name" => "Guyanaese Dollar", "code" => "GYD", "symbol" => "$"],
        ["name" => "Haitian Gourde", "code" => "HTG", "symbol" => "G"],
        ["name" => "Honduran Lempira", "code" => "HNL", "symbol" => "L"],
        ["name" => "Hong Kong Dollar", "code" => "HKD", "symbol" => "$"],
        ["name" => "Hungarian Forint", "code" => "HUF", "symbol" => "Ft"],
        ["name" => "Icelandic Króna", "code" => "ISK", "symbol" => "kr"],
        ["name" => "Indian Rupee", "code" => "INR", "symbol" => "₹"],
        ["name" => "Indonesian Rupiah", "code" => "IDR", "symbol" => "Rp"],
        ["name" => "Iranian Rial", "code" => "IRR", "symbol" => "﷼"],
        ["name" => "Iraqi Dinar", "code" => "IQD", "symbol" => "د.ع"],
        ["name" => "Israeli New Sheqel", "code" => "ILS", "symbol" => "₪"],
        ["name" => "Italian Lira", "code" => "ITL", "symbol" => "L,£"],
        ["name" => "Jamaican Dollar", "code" => "JMD", "symbol" => "J$"],
        ["name" => "Japanese Yen", "code" => "JPY", "symbol" => "¥"],
        ["name" => "Jordanian Dinar", "code" => "JOD", "symbol" => "ا.د"],
        ["name" => "Kazakhstani Tenge", "code" => "KZT", "symbol" => "лв"],
        ["name" => "Kenyan Shilling", "code" => "KES", "symbol" => "KSh"],
        ["name" => "Kuwaiti Dinar", "code" => "KWD", "symbol" => "ك.د"],
        ["name" => "Kyrgystani Som", "code" => "KGS", "symbol" => "лв"],
        ["name" => "Laotian Kip", "code" => "LAK", "symbol" => "₭"],
        ["name" => "Latvian Lats", "code" => "LVL", "symbol" => "Ls"],
        ["name" => "Lebanese Pound", "code" => "LBP", "symbol" => "£"],
        ["name" => "Lesotho Loti", "code" => "LSL", "symbol" => "L"],
        ["name" => "Liberian Dollar", "code" => "LRD", "symbol" => "$"],
        ["name" => "Libyan Dinar", "code" => "LYD", "symbol" => "د.ل"],
        ["name" => "Litecoin", "code" => "LTC", "symbol" => "Ł"],
        ["name" => "Lithuanian Litas", "code" => "LTL", "symbol" => "Lt"],
        ["name" => "Macanese Pataca", "code" => "MOP", "symbol" => "$"],
        ["name" => "Macedonian Denar", "code" => "MKD", "symbol" => "ден"],
        ["name" => "Malagasy Ariary", "code" => "MGA", "symbol" => "Ar"],
        ["name" => "Malawian Kwacha", "code" => "MWK", "symbol" => "MK"],
        ["name" => "Malaysian Ringgit", "code" => "MYR", "symbol" => "RM"],
        ["name" => "Maldivian Rufiyaa", "code" => "MVR", "symbol" => "Rf"],
        ["name" => "Mauritanian Ouguiya", "code" => "MRO", "symbol" => "MRU"],
        ["name" => "Mauritian Rupee", "code" => "MUR", "symbol" => "₨"],
        ["name" => "Mexican Peso", "code" => "MXN", "symbol" => "$"],
        ["name" => "Moldovan Leu", "code" => "MDL", "symbol" => "L"],
        ["name" => "Mongolian Tugrik", "code" => "MNT", "symbol" => "₮"],
        ["name" => "Moroccan Dirham", "code" => "MAD", "symbol" => "MAD"],
        ["name" => "Mozambican Metical", "code" => "MZM", "symbol" => "MT"],
        ["name" => "Myanmar Kyat", "code" => "MMK", "symbol" => "K"],
        ["name" => "Namibian Dollar", "code" => "NAD", "symbol" => "$"],
        ["name" => "Nepalese Rupee", "code" => "NPR", "symbol" => "₨"],
        ["name" => "Netherlands Antillean Guilder", "code" => "ANG", "symbol" => "ƒ"],
        ["name" => "New Taiwan Dollar", "code" => "TWD", "symbol" => "$"],
        ["name" => "New Zealand Dollar", "code" => "NZD", "symbol" => "$"],
        ["name" => "Nicaraguan Córdoba", "code" => "NIO", "symbol" => "C$"],
        ["name" => "Nigerian Naira", "code" => "NGN", "symbol" => "₦"],
        ["name" => "North Korean Won", "code" => "KPW", "symbol" => "₩"],
        ["name" => "Norwegian Krone", "code" => "NOK", "symbol" => "kr"],
        ["name" => "Omani Rial", "code" => "OMR", "symbol" => ".ع.ر"],
        ["name" => "Pakistani Rupee", "code" => "PKR", "symbol" => "₨"],
        ["name" => "Panamanian Balboa", "code" => "PAB", "symbol" => "B/."],
        ["name" => "Papua New Guinean Kina", "code" => "PGK", "symbol" => "K"],
        ["name" => "Paraguayan Guarani", "code" => "PYG", "symbol" => "₲"],
        ["name" => "Peruvian Nuevo Sol", "code" => "PEN", "symbol" => "S/."],
        ["name" => "Philippine Peso", "code" => "PHP", "symbol" => "₱"],
        ["name" => "Polish Zloty", "code" => "PLN", "symbol" => "zł"],
        ["name" => "Qatari Rial", "code" => "QAR", "symbol" => "ق.ر"],
        ["name" => "Romanian Leu", "code" => "RON", "symbol" => "lei"],
        ["name" => "Russian Ruble", "code" => "RUB", "symbol" => "₽"],
        ["name" => "Rwandan Franc", "code" => "RWF", "symbol" => "FRw"],
        ["name" => "Salvadoran Colón", "code" => "SVC", "symbol" => "₡"],
        ["name" => "Samoan Tala", "code" => "WST", "symbol" => "SAT"],
        ["name" => "São Tomé and Príncipe Dobra", "code" => "STD", "symbol" => "Db"],
        ["name" => "Saudi Riyal", "code" => "SAR", "symbol" => "﷼"],
        ["name" => "Serbian Dinar", "code" => "RSD", "symbol" => "din"],
        ["name" => "Seychellois Rupee", "code" => "SCR", "symbol" => "SRe"],
        ["name" => "Sierra Leonean Leone", "code" => "SLL", "symbol" => "Le"],
        ["name" => "Singapore Dollar", "code" => "SGD", "symbol" => "$"],
        ["name" => "Slovak Koruna", "code" => "SKK", "symbol" => "Sk"],
        ["name" => "Solomon Islands Dollar", "code" => "SBD", "symbol" => "Si$"],
        ["name" => "Somali Shilling", "code" => "SOS", "symbol" => "Sh.so."],
        ["name" => "South African Rand", "code" => "ZAR", "symbol" => "R"],
        ["name" => "South Korean Won", "code" => "KRW", "symbol" => "₩"],
        ["name" => "South Sudanese Pound", "code" => "SSP", "symbol" => "£"],
        ["name" => "Special Drawing Rights", "code" => "XDR", "symbol" => "SDR"],
        ["name" => "Sri Lankan Rupee", "code" => "LKR", "symbol" => "Rs"],
        ["name" => "St. Helena Pound", "code" => "SHP", "symbol" => "£"],
        ["name" => "Sudanese Pound", "code" => "SDG", "symbol" => ".س.ج"],
        ["name" => "Surinamese Dollar", "code" => "SRD", "symbol" => "$"],
        ["name" => "Swazi Lilangeni", "code" => "SZL", "symbol" => "E"],
        ["name" => "Swedish Krona", "code" => "SEK", "symbol" => "kr"],
        ["name" => "Swiss Franc", "code" => "CHF", "symbol" => "CHf"],
        ["name" => "Syrian Pound", "code" => "SYP", "symbol" => "LS"],
        ["name" => "Tajikistani Somoni", "code" => "TJS", "symbol" => "SM"],
        ["name" => "Tanzanian Shilling", "code" => "TZS", "symbol" => "TSh"],
        ["name" => "Thai Baht", "code" => "THB", "symbol" => "฿"],
        ["name" => "Tongan Pa'anga", "code" => "TOP", "symbol" => "$"],
        ["name" => "Trinidad & Tobago Dollar", "code" => "TTD", "symbol" => "$"],
        ["name" => "Tunisian Dinar", "code" => "TND", "symbol" => "ت.د"],
        ["name" => "Turkish Lira", "code" => "TRY", "symbol" => "₺"],
        ["name" => "Turkmenistani Manat", "code" => "TMT", "symbol" => "T"],
        ["name" => "Ugandan Shilling", "code" => "UGX", "symbol" => "USh"],
        ["name" => "Ukrainian Hryvnia", "code" => "UAH", "symbol" => "₴"],
        ["name" => "United Arab Emirates Dirham", "code" => "AED", "symbol" => "إ.د"],
        ["name" => "Uruguayan Peso", "code" => "UYU", "symbol" => "$"],
        ["name" => "US Dollar", "code" => "USD", "symbol" => "$"],
        ["name" => "Uzbekistan Som", "code" => "UZS", "symbol" => "лв"],
        ["name" => "Vanuatu Vatu", "code" => "VUV", "symbol" => "VT"],
        ["name" => "Venezuelan Bolívar", "code" => "VEF", "symbol" => "Bs"],
        ["name" => "Vietnamese Dong", "code" => "VND", "symbol" => "₫"],
        ["name" => "Yemeni Rial", "code" => "YER", "symbol" => "﷼"],
        ["name" => "Zambian Kwacha", "code" => "ZMK", "symbol" => "ZK"],
        ["name" => "Zimbabwean dollar", "code" => "ZWL", "symbol" => "$"]

    ];





    return $currencies;
}

//---------------------------------------------------

function vh_get_country_languages()
{
    $languages = [
        ["code" => "aa", "name" => "Afar"],
        ["code" => "ab", "name" => "Abkhazian"],
        ["code" => "ae", "name" => "Avestan"],
        ["code" => "af", "name" => "Afrikaans"],
        ["code" => "ak", "name" => "Akan"],
        ["code" => "am", "name" => "Amharic"],
        ["code" => "an", "name" => "Aragonese"],
        ["code" => "ar", "name" => "Arabic"],
        ["code" => "as", "name" => "Assamese"],
        ["code" => "av", "name" => "Avaric"],
        ["code" => "ay", "name" => "Aymara"],
        ["code" => "az", "name" => "Azerbaijani"],
        ["code" => "ba", "name" => "Bashkir"],
        ["code" => "be", "name" => "Belarusian"],
        ["code" => "bg", "name" => "Bulgarian"],
        ["code" => "bh", "name" => "Bihari languages"],
        ["code" => "bi", "name" => "Bislama"],
        ["code" => "bm", "name" => "Bambara"],
        ["code" => "bn", "name" => "Bengali"],
        ["code" => "bo", "name" => "Tibetan"],
        ["code" => "br", "name" => "Breton"],
        ["code" => "bs", "name" => "Bosnian"],
        ["code" => "ca", "name" => "Catalan; Valencian"],
        ["code" => "ce", "name" => "Chechen"],
        ["code" => "ch", "name" => "Chamorro"],
        ["code" => "co", "name" => "Corsican"],
        ["code" => "cr", "name" => "Cree"],
        ["code" => "cs", "name" => "Czech"],
        ["code" => "cu", "name" => "Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic"],
        ["code" => "cv", "name" => "Chuvash"],
        ["code" => "cy", "name" => "Welsh"],
        ["code" => "da", "name" => "Danish"],
        ["code" => "de", "name" => "German"],
        ["code" => "dv", "name" => "Divehi; Dhivehi; Maldivian"],
        ["code" => "dz", "name" => "Dzongkha"],
        ["code" => "ee", "name" => "Ewe"],
        ["code" => "el", "name" => "Greek, Modern (1453-)"],
        ["code" => "en", "name" => "English"],
        ["code" => "eo", "name" => "Esperanto"],
        ["code" => "es", "name" => "Spanish; Castilian"],
        ["code" => "et", "name" => "Estonian"],
        ["code" => "eu", "name" => "Basque"],
        ["code" => "fa", "name" => "Persian"],
        ["code" => "ff", "name" => "Fulah"],
        ["code" => "fi", "name" => "Finnish"],
        ["code" => "fj", "name" => "Fijian"],
        ["code" => "fo", "name" => "Faroese"],
        ["code" => "fr", "name" => "French"],
        ["code" => "fy", "name" => "Western Frisian"],
        ["code" => "ga", "name" => "Irish"],
        ["code" => "gd", "name" => "Gaelic; Scomttish Gaelic"],
        ["code" => "gl", "name" => "Galician"],
        ["code" => "gn", "name" => "Guarani"],
        ["code" => "gu", "name" => "Gujarati"],
        ["code" => "gv", "name" => "Manx"],
        ["code" => "ha", "name" => "Hausa"],
        ["code" => "he", "name" => "Hebrew"],
        ["code" => "hi", "name" => "Hindi"],
        ["code" => "ho", "name" => "Hiri Motu"],
        ["code" => "hr", "name" => "Croatian"],
        ["code" => "ht", "name" => "Haitian; Haitian Creole"],
        ["code" => "hu", "name" => "Hungarian"],
        ["code" => "hy", "name" => "Armenian"],
        ["code" => "hz", "name" => "Herero"],
        ["code" => "ia", "name" => "Interlingua (International Auxiliary Language Association)"],
        ["code" => "id", "name" => "Indonesian"],
        ["code" => "ie", "name" => "Interlingue; Occidental"],
        ["code" => "ig", "name" => "Igbo"],
        ["code" => "ii", "name" => "Sichuan Yi; Nuosu"],
        ["code" => "ik", "name" => "Inupiaq"],
        ["code" => "io", "name" => "Ido"],
        ["code" => "is", "name" => "Icelandic"],
        ["code" => "it", "name" => "Italian"],
        ["code" => "iu", "name" => "Inuktitut"],
        ["code" => "ja", "name" => "Japanese"],
        ["code" => "jv", "name" => "Javanese"],
        ["code" => "ka", "name" => "Georgian"],
        ["code" => "kg", "name" => "Kongo"],
        ["code" => "ki", "name" => "Kikuyu; Gikuyu"],
        ["code" => "kj", "name" => "Kuanyama; Kwanyama"],
        ["code" => "kk", "name" => "Kazakh"],
        ["code" => "kl", "name" => "Kalaallisut; Greenlandic"],
        ["code" => "km", "name" => "Central Khmer"],
        ["code" => "kn", "name" => "Kannada"],
        ["code" => "ko", "name" => "Korean"],
        ["code" => "kr", "name" => "Kanuri"],
        ["code" => "ks", "name" => "Kashmiri"],
        ["code" => "ku", "name" => "Kurdish"],
        ["code" => "kv", "name" => "Komi"],
        ["code" => "kw", "name" => "Cornish"],
        ["code" => "ky", "name" => "Kirghiz; Kyrgyz"],
        ["code" => "la", "name" => "Latin"],
        ["code" => "lb", "name" => "Luxembourgish; Letzeburgesch"],
        ["code" => "lg", "name" => "Ganda"],
        ["code" => "li", "name" => "Limburgan; Limburger; Limburgish"],
        ["code" => "ln", "name" => "Lingala"],
        ["code" => "lo", "name" => "Lao"],
        ["code" => "lt", "name" => "Lithuanian"],
        ["code" => "lu", "name" => "Luba-Katanga"],
        ["code" => "lv", "name" => "Latvian"],
        ["code" => "mg", "name" => "Malagasy"],
        ["code" => "mh", "name" => "Marshallese"],
        ["code" => "mi", "name" => "Maori"],
        ["code" => "mk", "name" => "Macedonian"],
        ["code" => "ml", "name" => "Malayalam"],
        ["code" => "mn", "name" => "Mongolian"],
        ["code" => "mr", "name" => "Marathi"],
        ["code" => "ms", "name" => "Malay"],
        ["code" => "mt", "name" => "Maltese"],
        ["code" => "my", "name" => "Burmese"],
        ["code" => "na", "name" => "Nauru"],
        ["code" => "nb", "name" => "Bokmål, Norwegian; Norwegian Bokmål"],
        ["code" => "nd", "name" => "Ndebele, North; North Ndebele"],
        ["code" => "ne", "name" => "Nepali"],
        ["code" => "ng", "name" => "Ndonga"],
        ["code" => "nl", "name" => "Dutch; Flemish"],
        ["code" => "nn", "name" => "Norwegian Nynorsk; Nynorsk, Norwegian"],
        ["code" => "no", "name" => "Norwegian"],
        ["code" => "nr", "name" => "Ndebele, South; South Ndebele"],
        ["code" => "nv", "name" => "Navajo; Navaho"],
        ["code" => "ny", "name" => "Chichewa; Chewa; Nyanja"],
        ["code" => "oc", "name" => "Occitan (post 1500)"],
        ["code" => "oj", "name" => "Ojibwa"],
        ["code" => "om", "name" => "Oromo"],
        ["code" => "or", "name" => "Oriya"],
        ["code" => "os", "name" => "Ossetian; Ossetic"],
        ["code" => "pa", "name" => "Panjabi; Punjabi"],
        ["code" => "pi", "name" => "Pali"],
        ["code" => "pl", "name" => "Polish"],
        ["code" => "ps", "name" => "Pushto; Pashto"],
        ["code" => "pt", "name" => "Portuguese"],
        ["code" => "qu", "name" => "Quechua"],
        ["code" => "rm", "name" => "Romansh"],
        ["code" => "rn", "name" => "Rundi"],
        ["code" => "ro", "name" => "Romanian; Moldavian; Moldovan"],
        ["code" => "ru", "name" => "Russian"],
        ["code" => "rw", "name" => "Kinyarwanda"],
        ["code" => "sa", "name" => "Sanskrit"],
        ["code" => "sc", "name" => "Sardinian"],
        ["code" => "sd", "name" => "Sindhi"],
        ["code" => "se", "name" => "Northern Sami"],
        ["code" => "sg", "name" => "Sango"],
        ["code" => "si", "name" => "Sinhala; Sinhalese"],
        ["code" => "sk", "name" => "Slovak"],
        ["code" => "sl", "name" => "Slovenian"],
        ["code" => "sm", "name" => "Samoan"],
        ["code" => "sn", "name" => "Shona"],
        ["code" => "so", "name" => "Somali"],
        ["code" => "sq", "name" => "Albanian"],
        ["code" => "sr", "name" => "Serbian"],
        ["code" => "ss", "name" => "Swati"],
        ["code" => "st", "name" => "Sotho, Southern"],
        ["code" => "su", "name" => "Sundanese"],
        ["code" => "sv", "name" => "Swedish"],
        ["code" => "sw", "name" => "Swahili"],
        ["code" => "ta", "name" => "Tamil"],
        ["code" => "te", "name" => "Telugu"],
        ["code" => "tg", "name" => "Tajik"],
        ["code" => "th", "name" => "Thai"],
        ["code" => "ti", "name" => "Tigrinya"],
        ["code" => "tk", "name" => "Turkmen"],
        ["code" => "tl", "name" => "Tagalog"],
        ["code" => "tn", "name" => "Tswana"],
        ["code" => "to", "name" => "Tonga (Tonga Islands)"],
        ["code" => "tr", "name" => "Turkish"],
        ["code" => "ts", "name" => "Tsonga"],
        ["code" => "tt", "name" => "Tatar"],
        ["code" => "tw", "name" => "Twi"],
        ["code" => "ty", "name" => "Tahitian"],
        ["code" => "ug", "name" => "Uighur; Uyghur"],
        ["code" => "uk", "name" => "Ukrainian"],
        ["code" => "ur", "name" => "Urdu"],
        ["code" => "uz", "name" => "Uzbek"],
        ["code" => "ve", "name" => "Venda"],
        ["code" => "vi", "name" => "Vietnamese"],
        ["code" => "vo", "name" => "Volapük"],
        ["code" => "wa", "name" => "Walloon"],
        ["code" => "wo", "name" => "Wolof"],
        ["code" => "xh", "name" => "Xhosa"],
        ["code" => "yi", "name" => "Yiddish"],
        ["code" => "yo", "name" => "Yoruba"],
        ["code" => "za", "name" => "Zhuang; Chuang"],
        ["code" => "zh", "name" => "Chinese"],
        ["code" => "zu", "name" => "Zulu"]
    ];
    return $languages;
}

//---------------------------------------------------
//---------------------------------------------------



