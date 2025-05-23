const members = [
    {
        "title": "",
        "gender": 1,
        "firstName": "Max",
        "lastName": "Mustermann",
        "dateofbirth": "1985-04-12",
        "street": "Musterstraße 123",
        "zip": "12345",
        "city": "Musterstadt",
        "countrycode": "DE",
        "email": "max.mustermann@example.com",
        "tel": "+49 170 1234567",
        "iban": "DE89370400440532013000",
        "bic": "COBADEFFXXX",
        "sepa": true,
        "membership": 5,
        "password": "Test1234!"
    },
    {
        "title": "",
        "gender": 0,
        "firstName": "Erika",
        "lastName": "Musterfrau",
        "dateofbirth": "1992-08-30",
        "street": "Beispielweg 45",
        "zip": "67890",
        "city": "Beispielstadt",
        "countrycode": "AT",
        "email": "erika.musterfrau@example.com",
        "tel": "+43 664 9876543",
        "iban": "",
        "bic": "",
        "sepa": false,
        "membership": 7,
        "password": "Passwort!2024"
    },
    {
        "title": "Dr.",
        "gender": 2,
        "firstName": "Alex",
        "lastName": "Beispiel",
        "dateofbirth": "2000-01-15",
        "street": "Neutralstraße 12a",
        "zip": "54321",
        "city": "Diversstadt",
        "countrycode": "CH",
        "email": "alex.beispiel@example.com",
        "tel": "+41 76 1122334",
        "iban": "",
        "bic": "",
        "sepa": false,
        "membership": 7,
        "password": "SafePass123!"
    },
    {
        "title": "Dr.",
        "gender": 1,
        "firstName": "Jean",
        "lastName": "Dupont",
        "dateofbirth": "1978-11-05",
        "street": "Rue de Paris 3",
        "zip": "75001",
        "city": "Paris",
        "countrycode": "FR",
        "email": "jean.dupont@example.fr",
        "tel": "+33 6 12345678",
        "iban": "FR7630006000011234567890189",
        "bic": "AGRIFRPPXXX",
        "sepa": false,
        "membership": 5,
        "password": "Motdepasse@123"
    },
    {
        "title": "",
        "gender": 0,
        "firstName": "Sophie",
        "lastName": "de Vries",
        "dateofbirth": "1990-07-21",
        "street": "Kerkstraat 7",
        "zip": "1017",
        "city": "Amsterdam",
        "countrycode": "NL",
        "email": "sophie.devries@example.nl",
        "tel": "+31 6 87654321",
        "iban": "NL91ABNA0417164300",
        "bic": "",
        "sepa": true,
        "membership": 8,
        "password": "Welkom2025!"
    },
    {
        "title": "BSc.",
        "gender": 2,
        "firstName": "Luca",
        "lastName": "Bianchi",
        "dateofbirth": "1982-03-14",
        "street": "Via Roma 10",
        "zip": "00100",
        "city": "Roma",
        "countrycode": "IT",
        "email": "luca.bianchi@example.it",
        "tel": "+39 347 1234567",
        "iban": "IT60X0542811101000000123456",
        "bic": "",
        "sepa": true,
        "membership": 8,
        "password": "Italia@2025"
    },
    {
        "title": "",
        "gender": 1,
        "firstName": "Carlos",
        "lastName": "García",
        "dateofbirth": "1988-09-25",
        "street": "Calle Mayor 22",
        "zip": "28013",
        "city": "Madrid",
        "countrycode": "ES",
        "email": "carlos.garcia@example.es",
        "tel": "+34 600 123456",
        "iban": "ES9121000418450200051332",
        "bic": "CAIXESBBXXX",
        "sepa": false,
        "membership": 7,
        "password": "España2025!"
    },
    {
        "title": "",
        "gender": 0,
        "firstName": "Anna",
        "lastName": "Larsen",
        "dateofbirth": "1995-12-11",
        "street": "Storgatan 5",
        "zip": "11122",
        "city": "Stockholm",
        "countrycode": "SE",
        "email": "anna.larsen@example.se",
        "tel": "+46 70 1234567",
        "iban": "",
        "bic": "",
        "sepa": false,
        "membership": 5,
        "password": "Sverige2025!"
    },
    {
        "title": "MSc",
        "gender": 1,
        "firstName": "Jan",
        "lastName": "Kowalski",
        "dateofbirth": "1983-06-09",
        "street": "Ulica Polska 9",
        "zip": "00-001",
        "city": "Warszawa",
        "countrycode": "PL",
        "email": "jan.kowalski@example.pl",
        "tel": "+48 512 345678",
        "iban": "",
        "bic": "WBKPPLPP",
        "sepa": false,
        "membership": 8,
        "password": "Polska2025!"
    },
    {
        "title": "Dr.",
        "gender": 2,
        "firstName": "Kari",
        "lastName": "Hansen",
        "dateofbirth": "1975-05-05",
        "street": "Olav Tryggvasons gate 13",
        "zip": "7011",
        "city": "Trondheim",
        "countrycode": "NO",
        "email": "kari.hansen@example.no",
        "tel": "+47 934 56 789",
        "iban": "NO9386011117947",
        "bic": "",
        "sepa": true,
        "membership": 5,
        "password": "Norge2025!"
    },
    {
        "title": "",
        "gender": 1,
        "firstName": "Anders",
        "lastName": "Nielsen",
        "dateofbirth": "1980-10-18",
        "street": "Nørregade 1",
        "zip": "1165",
        "city": "København",
        "countrycode": "DK",
        "email": "anders.nielsen@example.dk",
        "tel": "+45 30 12 34 56",
        "iban": "DK5000400440116243",
        "bic": "DABADKKK",
        "sepa": true,
        "membership": 7,
        "password": "Danmark2025!"
    },
    {
        "title": "",
        "gender": 0,
        "firstName": "Laura",
        "lastName": "Virtanen",
        "dateofbirth": "1999-02-03",
        "street": "Mannerheimintie 20",
        "zip": "00100",
        "city": "Helsinki",
        "countrycode": "FI",
        "email": "laura.virtanen@example.fi",
        "tel": "+358 40 1234567",
        "iban": "",
        "bic": "",
        "sepa": false,
        "membership": 7,
        "password": "Suomi2025!"
    }
]

export { members };
