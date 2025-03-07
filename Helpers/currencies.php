<?php
function vh_st_get_currency_by_country_currency_code($country_code)
{
    $currencies = vh_st_get_country_currencies();

    $country = vh_st_search_currencies($currencies, 'code', $country_code);

    return $country;
}
//---------------------------------------------------

function vh_st_get_currency_by_country_currency_name($country_name)
{
    $currencies = vh_st_get_country_currencies();

    return vh_st_search_currencies($currencies, 'name', $country_name);
}

//---------------------------------------------------

function vh_st_get_currency_by_currency($currency)
{
    $currencies = vh_st_get_country_currencies();

    return vh_st_search_currencies($currencies, 'currencies', $currency);
}

//---------------------------------------------------
//---------------------------------------------------
//---------------------------------------------------

function vh_st_search_currencies($array, $key_name, $value)
{
    foreach($array as $key => $array_item)
    {
        if ( $array_item[$key_name] === $value )
            return $array[$key];
    }

    return false;
}

//---------------------------------------------------

function vh_st_get_currency_list_select_options_on_currencies($show = 'country_name')
{
    $html = "";
    $list = vh_st_get_country_currencies();

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

function vh_st_get_country_currencies()
{

    $currencies = [
        ["name" => "Afghan Afghani", "code" => "AFA", "symbol" => "&#1547;"],
        ["name" => "Albanian Lek", "code" => "ALL", "symbol" => "Lek"],
        ["name" => "Algerian Dinar", "code" => "DZD", "symbol" => "&#1583;&#1580;"],
        ["name" => "Angolan Kwanza", "code" => "AOA", "symbol" => "Kz"],
        ["name" => "Argentine Peso", "code" => "ARS", "symbol" => "&#36;"],
        ["name" => "Armenian Dram", "code" => "AMD", "symbol" => "&#1423;"],
        ["name" => "Aruban Florin", "code" => "AWG", "symbol" => "&#402;"],
        ["name" => "Australian Dollar", "code" => "AUD", "symbol" => "&#36;"],
        ["name" => "Azerbaijani Manat", "code" => "AZN", "symbol" => "m"],
        ["name" => "Bahamian Dollar", "code" => "BSD", "symbol" => "B&#36;"],
        ["name" => "Bahraini Dinar", "code" => "BHD", "symbol" => "&#x2E;&#x62F;&#x2E;&#x628;"],
        ["name" => "Bangladeshi Taka", "code" => "BDT", "symbol" => "&#2547;"],
        ["name" => "Barbadian Dollar", "code" => "BBD", "symbol" => "Bds&#36;"],
        ["name" => "Belarusian Ruble", "code" => "BYR", "symbol" => "Br"],
        ["name" => "Belgian Franc", "code" => "BEF", "symbol" => "fr"],
        ["name" => "Belize Dollar", "code" => "BZD", "symbol" => "&#36;"],
        ["name" => "Bermudan Dollar", "code" => "BMD", "symbol" => "&#36;"],
        ["name" => "Bhutanese Ngultrum", "code" => "BTN", "symbol" => "Nu."],
        ["name" => "Bitcoin", "code" => "BTC", "symbol" => "&#3647;"],
        ["name" => "Bolivian Boliviano", "code" => "BOB", "symbol" => "Bs."],
        ["name" => "Bosnia-Herzegovina Convertible Mark", "code" => "BAM", "symbol" => "KM"],
        ["name" => "Botswanan Pula", "code" => "BWP", "symbol" => "P"],
        ["name" => "Brazilian Real", "code" => "BRL", "symbol" => "R&#36;"],
        ["name" => "British Pound Sterling", "code" => "GBP", "symbol" => "&#163;"],
        ["name" => "Brunei Dollar", "code" => "BND", "symbol" => "B&#36;"],
        ["name" => "Bulgarian Lev", "code" => "BGN", "symbol" => "&#1051;&#1074;."],
        ["name" => "Burundian Franc", "code" => "BIF", "symbol" => "FBu"],
        ["name" => "Cambodian Riel", "code" => "KHR", "symbol" => "KHR"],
        ["name" => "Canadian Dollar", "code" => "CAD", "symbol" => "&#36;"],
        ["name" => "Cape Verdean Escudo", "code" => "CVE", "symbol" => "&#36;"],
        ["name" => "Cayman Islands Dollar", "code" => "KYD", "symbol" => "&#36;"],
        ["name" => "CFA Franc BCEAO", "code" => "XOF", "symbol" => "CFA"],
        ["name" => "CFA Franc BEAC", "code" => "XAF", "symbol" => "FCFA"],
        ["name" => "CFP Franc", "code" => "XPF", "symbol" => "&#8355;"],
        ["name" => "Chilean Peso", "code" => "CLP", "symbol" => "&#36;"],
        ["name" => "Chilean Unit of Account", "code" => "CLF", "symbol" => "CLF"],
        ["name" => "Chinese Yuan", "code" => "CNY", "symbol" => "&#165;"],
        ["name" => "Colombian Peso", "code" => "COP", "symbol" => "&#36;"],
        ["name" => "Comorian Franc", "code" => "KMF", "symbol" => "CF"],
        ["name" => "Congolese Franc", "code" => "CDF", "symbol" => "FC"],
        ["name" => "Costa Rican Colón", "code" => "CRC", "symbol" => "&#8353;"],
        ["name" => "Croatian Kuna", "code" => "HRK", "symbol" => "kn"],
        ["name" => "Cuban Convertible Peso", "code" => "CUC", "symbol" => "&#36;, CUC"],
        ["name" => "Czech Republic Koruna", "code" => "CZK", "symbol" => "K&#269;"],
        ["name" => "Danish Krone", "code" => "DKK", "symbol" => "Kr."],
        ["name" => "Djiboutian Franc", "code" => "DJF", "symbol" => "Fdj"],
        ["name" => "Dominican Peso", "code" => "DOP", "symbol" => "&#36;"],
        ["name" => "East Caribbean Dollar", "code" => "XCD", "symbol" => "&#36;"],
        ["name" => "Egyptian Pound", "code" => "EGP", "symbol" => "&#1580;.&#1605;"],
        ["name" => "Eritrean Nakfa", "code" => "ERN", "symbol" => "Nfk"],
        ["name" => "Estonian Kroon", "code" => "EEK", "symbol" => "kr"],
        ["name" => "Ethiopian Birr", "code" => "ETB", "symbol" => "Nkf"],
        ["name" => "Euro", "code" => "EUR", "symbol" => "&#8364;"],
        ["name" => "Falkland Islands Pound", "code" => "FKP", "symbol" => "&#163;"],
        ["name" => "Fijian Dollar", "code" => "FJD", "symbol" => "FJ&#36;"],
        ["name" => "Gambian Dalasi", "code" => "GMD", "symbol" => "D"],
        ["name" => "Georgian Lari", "code" => "GEL", "symbol" => "&#x20BE;"],
        ["name" => "German Mark", "code" => "DEM", "symbol" => "DM"],
        ["name" => "Ghanaian Cedi", "code" => "GHS", "symbol" => "GH&#x20B5;"],
        ["name" => "Gibraltar Pound", "code" => "GIP", "symbol" => "&pound;"],
        ["name" => "Greek Drachma", "code" => "GRD", "symbol" => "&#x20AF;, &#x394;&#x41C;&#x3C1;, &#x394;&#x3C1;"],
        ["name" => "Guatemalan Quetzal", "code" => "GTQ", "symbol" => "Q"],
        ["name" => "Guinean Franc", "code" => "GNF", "symbol" => "FG"],
        ["name" => "Guyanaese Dollar", "code" => "GYD", "symbol" => "&#x24;"],
        ["name" => "Haitian Gourde", "code" => "HTG", "symbol" => "G"],
        ["name" => "Honduran Lempira", "code" => "HNL", "symbol" => "L"],
        ["name" => "Hong Kong Dollar", "code" => "HKD", "symbol" => "&#x24;"],
        ["name" => "Hungarian Forint", "code" => "HUF", "symbol" => "&#x46;&#x74;"],
        ["name" => "Icelandic Króna", "code" => "ISK", "symbol" => "kr"],
        ["name" => "Indian Rupee", "code" => "INR", "symbol" => "&#x20B9;"],
        ["name" => "Indonesian Rupiah", "code" => "IDR", "symbol" => "Rp"],
        ["name" => "Iranian Rial", "code" => "IRR", "symbol" => "&#xFDFC;"],
        ["name" => "Iraqi Dinar", "code" => "IQD", "symbol" => "&#x639;.&#x62F;"],
        ["name" => "Israeli New Sheqel", "code" => "ILS", "symbol" => "&#x20AA;"],
        ["name" => "Italian Lira", "code" => "ITL", "symbol" => "L,&pound;"],
        ["name" => "Jamaican Dollar", "code" => "JMD", "symbol" => "J&#x24;"],
        ["name" => "Japanese Yen", "code" => "JPY", "symbol" => "&#xA5;"],
        ["name" => "Jordanian Dinar", "code" => "JOD", "symbol" => "&#x627;.&#x62F;"],
        ["name" => "Kazakhstani Tenge", "code" => "KZT", "symbol" => "&#x43B;&#x432;"],
        ["name" => "Kenyan Shilling", "code" => "KES", "symbol" => "KSh"],
        ["name" => "Kuwaiti Dinar", "code" => "KWD", "symbol" => "&#x643;.&#x62F;"],
        ["name" => "Kyrgystani Som", "code" => "KGS", "symbol" => "&#x43B;&#x432;"],
        ["name" => "Laotian Kip", "code" => "LAK", "symbol" => "&#x20AD;"],
        ["name" => "Latvian Lats", "code" => "LVL", "symbol" => "Ls"],
        ["name" => "Lebanese Pound", "code" => "LBP", "symbol" => "&pound;"],
        ["name" => "Lesotho Loti", "code" => "LSL", "symbol" => "L"],
        ["name" => "Liberian Dollar", "code" => "LRD", "symbol" => "$"],
        ["name" => "Libyan Dinar", "code" => "LYD", "symbol" => "&#x62F;&#x2E;&#x644;"], // د.ل
        ["name" => "Litecoin", "code" => "LTC", "symbol" => "Ł"],
        ["name" => "Lithuanian Litas", "code" => "LTL", "symbol" => "Lt"],
        ["name" => "Macanese Pataca", "code" => "MOP", "symbol" => "$"],
        ["name" => "Macedonian Denar", "code" => "MKD", "symbol" => "&#x434;&#x435;&#x43D;"], // ден
        ["name" => "Malagasy Ariary", "code" => "MGA", "symbol" => "Ar"],
        ["name" => "Malawian Kwacha", "code" => "MWK", "symbol" => "MK"],
        ["name" => "Malaysian Ringgit", "code" => "MYR", "symbol" => "RM"],
        ["name" => "Maldivian Rufiyaa", "code" => "MVR", "symbol" => "Rf"],
        ["name" => "Mauritanian Ouguiya", "code" => "MRO", "symbol" => "MRU"],
        ["name" => "Mauritian Rupee", "code" => "MUR", "symbol" => "&#x20A8;"], // ₨
        ["name" => "Mexican Peso", "code" => "MXN", "symbol" => "$"],
        ["name" => "Moldovan Leu", "code" => "MDL", "symbol" => "L"],
        ["name" => "Mongolian Tugrik", "code" => "MNT", "symbol" => "&#x20AE;"], // ₮
        ["name" => "Moroccan Dirham", "code" => "MAD", "symbol" => "MAD"],
        ["name" => "Mozambican Metical", "code" => "MZM", "symbol" => "MT"],
        ["name" => "Myanmar Kyat", "code" => "MMK", "symbol" => "K"],
        ["name" => "Namibian Dollar", "code" => "NAD", "symbol" => "$"],
        ["name" => "Nepalese Rupee", "code" => "NPR", "symbol" => "&#x20A8;"], // ₨
        ["name" => "Netherlands Antillean Guilder", "code" => "ANG", "symbol" => "&#x0192;"], // ƒ
        ["name" => "New Taiwan Dollar", "code" => "TWD", "symbol" => "$"],
        ["name" => "New Zealand Dollar", "code" => "NZD", "symbol" => "$"],
        ["name" => "Nicaraguan Córdoba", "code" => "NIO", "symbol" => "C$"],
        ["name" => "Nigerian Naira", "code" => "NGN", "symbol" => "&#x20A6;"], // ₦
        ["name" => "North Korean Won", "code" => "KPW", "symbol" => "&#x20A9;"], // ₩
        ["name" => "Norwegian Krone", "code" => "NOK", "symbol" => "kr"],
        ["name" => "Omani Rial", "code" => "OMR", "symbol" => "&#x2E;&#x639;&#x2E;&#x631;"], // .ع.ر
        ["name" => "Pakistani Rupee", "code" => "PKR", "symbol" => "&#x20A8;"], // ₨
        ["name" => "Panamanian Balboa", "code" => "PAB", "symbol" => "B/."],
        ["name" => "Papua New Guinean Kina", "code" => "PGK", "symbol" => "K"],
        ["name" => "Paraguayan Guarani", "code" => "PYG", "symbol" => "&#x20B2;"], // ₲
        ["name" => "Peruvian Nuevo Sol", "code" => "PEN", "symbol" => "S/."],
        ["name" => "Philippine Peso", "code" => "PHP", "symbol" => "&#x20B1;"], // ₱
        ["name" => "Polish Zloty", "code" => "PLN", "symbol" => "&#x007A;&#x0142;"], // zł
        ["name" => "Qatari Rial", "code" => "QAR", "symbol" => "&#x642;&#x2E;&#x631;"],
        ["name" => "Romanian Leu", "code" => "RON", "symbol" => "lei"],
        ["name" => "Russian Ruble", "code" => "RUB", "symbol" => "&#8381;"], // ₽
        ["name" => "Rwandan Franc", "code" => "RWF", "symbol" => "FRw"],
        ["name" => "Salvadoran Colón", "code" => "SVC", "symbol" => "&#162;"], // ₡
        ["name" => "Samoan Tala", "code" => "WST", "symbol" => "SAT"],
        ["name" => "São Tomé and Príncipe Dobra", "code" => "STD", "symbol" => "Db"],
        ["name" => "Saudi Riyal", "code" => "SAR", "symbol" => "&#xFDFC;"], // ﷼
        ["name" => "Serbian Dinar", "code" => "RSD", "symbol" => "din"],
        ["name" => "Seychellois Rupee", "code" => "SCR", "symbol" => "SRe"],
        ["name" => "Sierra Leonean Leone", "code" => "SLL", "symbol" => "Le"],
        ["name" => "Singapore Dollar", "code" => "SGD", "symbol" => "&#36;"], // $
        ["name" => "Slovak Koruna", "code" => "SKK", "symbol" => "Sk"],
        ["name" => "Solomon Islands Dollar", "code" => "SBD", "symbol" => "Si&#36;"], // Si$
        ["name" => "Somali Shilling", "code" => "SOS", "symbol" => "Sh.so."],
        ["name" => "South African Rand", "code" => "ZAR", "symbol" => "R"],
        ["name" => "South Korean Won", "code" => "KRW", "symbol" => "&#8361;"], // ₩
        ["name" => "South Sudanese Pound", "code" => "SSP", "symbol" => "&#163;"], // £
        ["name" => "Special Drawing Rights", "code" => "XDR", "symbol" => "SDR"],
        ["name" => "Sri Lankan Rupee", "code" => "LKR", "symbol" => "Rs"],
        ["name" => "St. Helena Pound", "code" => "SHP", "symbol" => "&#163;"], // £
        ["name" => "Sudanese Pound", "code" => "SDG", "symbol" => ".&#1587;.&#1580;"], // .س.ج
        ["name" => "Surinamese Dollar", "code" => "SRD", "symbol" => "&#36;"], // $
        ["name" => "Swazi Lilangeni", "code" => "SZL", "symbol" => "E"],
        ["name" => "Swedish Krona", "code" => "SEK", "symbol" => "kr"],
        ["name" => "Swiss Franc", "code" => "CHF", "symbol" => "CHf"],
        ["name" => "Syrian Pound", "code" => "SYP", "symbol" => "LS"],
        ["name" => "Tajikistani Somoni", "code" => "TJS", "symbol" => "SM"],
        ["name" => "Tanzanian Shilling", "code" => "TZS", "symbol" => "TSh"],
        ["name" => "Thai Baht", "code" => "THB", "symbol" => "&#3647;"], // ฿
        ["name" => "Tongan Pa'anga", "code" => "TOP", "symbol" => "&#36;"], // $
        ["name" => "Trinidad & Tobago Dollar", "code" => "TTD", "symbol" => "&#36;"], // $
        ["name" => "Tunisian Dinar", "code" => "TND", "symbol" => "&#1578;.&#1583;"], // ت.د
        ["name" => "Turkish Lira", "code" => "TRY", "symbol" => "&#8378;"], // ₺

        ["name" => "Turkmenistani Manat", "code" => "TMT", "symbol" => "T"],
        ["name" => "Ugandan Shilling", "code" => "UGX", "symbol" => "USh"],
        ["name" => "Ukrainian Hryvnia", "code" => "UAH", "symbol" => "&#8372;"], // ₴
        ["name" => "United Arab Emirates Dirham", "code" => "AED", "symbol" => "&#x626;.&#x62f;"], // إ.د
        ["name" => "Uruguayan Peso", "code" => "UYU", "symbol" => "&#36;"], // $
        ["name" => "US Dollar", "code" => "USD", "symbol" => "&#36;"], // $
        ["name" => "Uzbekistan Som", "code" => "UZS", "symbol" => "лв"],
        ["name" => "Vanuatu Vatu", "code" => "VUV", "symbol" => "VT"],
        ["name" => "Venezuelan Bolívar", "code" => "VEF", "symbol" => "Bs"],
        ["name" => "Vietnamese Dong", "code" => "VND", "symbol" => "&#8363;"], // ₫
        ["name" => "Yemeni Rial", "code" => "YER", "symbol" => "&#xFDFC;"], // ﷼
        ["name" => "Zambian Kwacha", "code" => "ZMK", "symbol" => "ZK"],
        ["name" => "Zimbabwean Dollar", "code" => "ZWL", "symbol" => "&#36;"], // $


    ];





    return $currencies;
}

//---------------------------------------------------

function vh_st_get_country_languages()
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



