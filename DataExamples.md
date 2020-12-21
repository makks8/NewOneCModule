## 1c -> bitrix

## Company
{
    GUID => string (req),
    TITLE => string (req),
    ASSIGNED_EMAIL => string (email),  
    PHONE => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    EMAIL => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    WEB => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    REQUISITE => {
        (поле реквизитов) => (значение)
    },
    (поле сделки) => (значение),
    (поле компании с привязкой к списку) => [
        { 
            "block_code" => string (req), 
            "element_guid" => string (req),
            "is_multiple" => bool, 
            "fields" => { 
                "NAME" => string (req),
                (поля списка)
            }
        }
    ],
    CONTACTS => [
        { (структура контакта) }
    ]
}

## Contact
{
    GUID => string (req),
    NAME => string (req or LAST_NAME),
    LAST_NAME => string (req or NAME),
    ASSIGNED_EMAIL => string (email),
    PHONE => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    EMAIL => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    WEB => [
        { 
            "VALUE" => string, 
            "VALUE_TYPE" => string 
        }
    ],
    (поле сделки) => (значение),
    (поле компании с привязкой к списку) => [
        { 
            "block_code" => string (req), 
            "element_guid" => string (req),
            "is_multiple" => bool, 
            "fields" => { 
                "NAME" => string (req),
                (поле списка) => (значение)
            }
        }
    ],
    COMPANIES => [
        { (структура компании) }
    ]
}

## Deal
{
    GUID => string (req),
    TITLE => string (req),
    COMPANY_ID => string (guid),
    ASSIGNED_EMAIL => string (email),
    (поле сделки) => (значение),
    PRODUCTS => [
        {
            GUID => string (req),
            QUANTITY => number,
            PRICE => number,
        }
    ]
}

## PRODUCT
{
    GUID => string,
    NAME => string,
    MEASURE => {
       CODE => string,
       MEASURE_TITLE => string,
       SYMBOL_RUS => string
    },
    VAT_ID => {
        NAME => string,
        RATE => number
    },
    CATALOG_ID => int,
    VAT_INCLUDED => Y/N,
    SECTION_ID => [
        {
            NAME => string,
            CODE => string
        }
    ]  
}

