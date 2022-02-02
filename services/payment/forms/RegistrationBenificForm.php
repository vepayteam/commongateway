<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PartnerBankGate;
use yii\base\DynamicModel;
use yii\base\Model;

class RegistrationBenificForm extends Model
{
    use ValidateFormTrait;
    /** @var Partner */
    public $partner;

    public $registrationtype;
    public $ownershipform;
    public $ownershipgroup;
    public $msp;
    public $legalformsofbusiness;
    public $capitalpayed;
    public $urname;
    public $shorturname;
    public $inn;
    public $kpp;
    public $ogrn;
    public $okved;
    public $okopf;
    public $okpo;
    public $govregistrationdate;
    public $govregistrationplace;
    public $govregistrationathority;
    public $lastname;
    public $firstname;
    public $middlename;
    public $dirregistrationdate;
    public $innfl;
    public $birthdate;
    public $birthplace;
    public $gender;
    public $pdl;
    public $rpdl;
    public $nationality;
    public $resident;
    public $document;
    public $righttostaydocument;
    public $phone;
    public $email;
    public $uraddres;
    public $actualaddres;
    public $groundforparticipation;
    public $joiningdate;

    public $result;

    private $docTypes = [
        0 => 'Other',
        1 => 'PassportRussianFederation',
        2 => 'PassportForeignCitizen',
        3 => 'ResidencePermits',
        4 => 'TemporaryResidencePermits',
        5 => 'Visa',
        6 => 'MigrationCard'
    ];

    private $ownershipFormTypes = [
        0 => 'None',
        1 => 'FinancialOrganization',
        2 => 'CommercialOrganization',
        3 => 'NonCommercialOrganization',
        4 => 'Individual'
    ];

    private $ownershipGroupTypes = [
        0 => 'None',
        1 => 'FederalOwnership',
        2 => 'GovernmentOwnership',
        3 => 'NonGovernmentOwnership',
        4 => 'IndividualOwnership'
    ];

    private $legalFormsOfBusinessTypes = [
        0 => 'Other',
        1 => 'OOO',
        2 => 'ZAO',
        3 => 'OAO',
        4 => 'AO',
        5 => 'PAO'
    ];

    public function rules()
    {
        return [
            [['msp', 'gender', 'pdl', 'rpdl', 'resident'], 'boolean'],
            [['registrationtype'], 'integer', 'max' => 2],
            [['ownershipform'], 'integer', 'max' => 4],
            [['ownershipgroup'], 'integer', 'max' => 4],
            [['legalformsofbusiness'], 'integer', 'max' => 5],
            [['capitalpayed'], 'number'],
            [['urname', 'shorturname', 'lastname', 'firstname', 'middlename', 'birthplace', 'nationality',
                'govregistrationplace', 'govregistrationathority', 'groundforparticipation'], 'string', 'max' => 500],
            [['inn', 'kpp', 'ogrn', 'okved', 'okopf', 'okpo', 'innfl', 'phone', 'email'], 'string', 'max' => 50],
            [['govregistrationdate', 'dirregistrationdate', 'birthdate', 'joiningdate'], 'date', 'format' => 'php:Y-m-d'],
            [['uraddres', 'actualaddres'], function ($attribute, $params) {
                $addr = new DynamicModel(['fias', 'country', 'region', 'regiontype', 'city', 'citytype', 'place',
                    'placetype', 'street', 'streettype', 'house', 'corp', 'flat']);
                $addr->addRule(['fias', 'country', 'region', 'regiontype', 'city', 'citytype', 'place',
                    'placetype', 'street', 'streettype', 'house', 'corp', 'flat'], 'string', ['max' => 255]);
                $addr->addRule(['fias', 'country', 'region'],'required');
                if ($addr->load($this->$attribute,'') && $addr->validate()) {
                    $this->$attribute = $addr;
                } else {
                    $this->addError($attribute, 'Неверный адрес');
                }
            }],
            [['document', 'righttostaydocument'], function ($attribute, $params) {
                $docum = new DynamicModel(['type', 'serial', 'number', 'issueby', 'date', 'finishdate', 'code']);
                $docum->addRule(['type'], 'integer', ['max' => 6]);
                $docum->addRule(['date', 'finishdate'], 'date', ['format' => 'php:Y-m-d']);
                $docum->addRule(['serial', 'number', 'issueby', 'code'], 'string', ['max' => 255]);
                $docum->addRule(['type', 'number', 'date'], 'required');
                if ($docum->load($this->$attribute,'') && $docum->validate()) {
                    $this->$attribute = $docum;
                } else {
                    $this->addError($attribute, 'Неверный документ');
                }
            }],

            ['result', 'safe'],
            [['lastname', 'firstname'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
        ];
    }

    /**
     * Создание SOAP запроса
     *
     * @param PartnerBankGate $partnerBankGate
     * @return string
     */
    public function buildSoapForm(PartnerBankGate $partnerBankGate)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $Envelope = $dom->createElement("soapenv:Envelope");
        $dom->appendChild($Envelope);
        $Envelope->setAttribute("xmlns:soapenv", "http://schemas.xmlsoap.org/soap/envelope/");
        $Envelope->setAttribute("xmlns:cft", "http://cft.transcapital.ru/CftNominalIntegrator/");
        $Envelope->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $Envelope->appendChild($dom->createElement("soapenv:Header"));
        $body = $dom->createElement("soapenv:Body");
        $Envelope->appendChild($body);
        $Request = $dom->createElement("cft:SetBeneficiaryRequest");
        $body->appendChild($Request);
        //$Request->appendChild($dom->createElement("pkgId"));
        $client = $dom->createElement("client");
        $Request->appendChild($client);

        if ($this->registrationtype == 1) {
            //ИП
            $client->appendChild($dom->createElement("RegistrationType", "Individual"));
            $Individual = $dom->createElement("Individual");
            $client->appendChild($Individual);

            $Individual->appendChild($dom->createElement("IdIndividual"));

            if ($this->document) {
                $IndividualIdentityDocument = $dom->createElement("IndividualIdentityDocument");
                $IndividualIdentityDocument->appendChild($dom->createElement("SerialNumber", $this->document->serial));
                $IndividualIdentityDocument->appendChild($dom->createElement("Number", $this->document->number));
                $IndividualIdentityDocument->appendChild($dom->createElement("IssueBy", $this->document->issueby));
                $IndividualIdentityDocument->appendChild($dom->createElement("Date", $this->document->date));
                $IndividualIdentityDocument->appendChild($dom->createElement("Code", $this->document->code));
                $Individual->appendChild($IndividualIdentityDocument);
            }

            $Individual->appendChild($dom->createElement("IndividualConfirmingRightToStayDocument"));

            if ($this->uraddres) {
                $RegistrationAddress = $dom->createElement("RegistrationAddress");
                $RegistrationAddress->appendChild($dom->createElement("Fias", $this->uraddres->fias));
                $RegistrationAddress->appendChild($dom->createElement("Country", $this->uraddres->country));
                $RegistrationAddress->appendChild($dom->createElement("Region", $this->uraddres->region));
                $RegistrationAddress->appendChild($dom->createElement("AddressCity", $this->uraddres->city));
                $RegistrationAddress->appendChild($dom->createElement("AddressStreet", $this->uraddres->street));
                $RegistrationAddress->appendChild($dom->createElement("AddressHouse", $this->uraddres->house));
                if (!empty($this->uraddres->flat)) {
                    $RegistrationAddress->appendChild($dom->createElement("AddressFlat", $this->uraddres->flat));
                }
                $RegistrationAddress->appendChild($dom->createElement("FullAddress", $this->getFullAddress($this->uraddres)));
                $Individual->appendChild($RegistrationAddress);
            }

            $Individual->appendChild($dom->createElement("ActivityKinds"));

            $ClientFinancialPosition = $dom->createElement("ClientFinancialPosition");
            $ClientFinancialPosition->appendChild($dom->createElement("ClientEvaluatesFinancialPosition","Missing"));
            $Individual->appendChild($ClientFinancialPosition);

            $Individual->appendChild($dom->createElement("Fio",$this->lastname . ' ' . $this->firstname . ' ' . $this->middlename));
            $Individual->appendChild($dom->createElement("LastName", $this->lastname));
            $Individual->appendChild($dom->createElement("FirstName", $this->firstname));
            if (!empty($this->middlename)) {
                $Individual->appendChild($dom->createElement("MiddleName", $this->middlename));
            }
            if (!empty($this->birthdate)) {
                $Individual->appendChild($dom->createElement("BirthDate", $this->birthdate));
            }
            if (!empty($this->birthplace)) {
                $Individual->appendChild($dom->createElement("BirthPlace", $this->birthplace));
            }
            $Individual->appendChild($dom->createElement("Gender",$this->gender ? 'Female' : 'Male'));
            $Individual->appendChild($dom->createElement("PDL", $this->pdl));
            $Individual->appendChild($dom->createElement("RPDL", $this->rpdl));
            $Individual->appendChild($dom->createElement("Nationality", $this->nationality));
            if ($this->document) {
                $Individual->appendChild($dom->createElement("IndividualIdentityDocumentType", $this->docTypes[$this->document->type]));
            }
            if (!empty($this->govregistrationdate)) {
                $Individual->appendChild($dom->createElement("GovRegistrationDate", $this->govregistrationdate));
            }
            if (!empty($this->govregistrationplace)) {
                $Individual->appendChild($dom->createElement("GovRegistrationPlace", $this->govregistrationplace));
            }
            if (!empty($this->govregistrationathority)) {
                $Individual->appendChild($dom->createElement("GovRegistrationAthority", $this->govregistrationathority));
            }
            $Individual->appendChild($dom->createElement("Inn", $this->inn));
            $Individual->appendChild($dom->createElement("Ogrnip", $this->ogrn));
            if (!empty($this->okved)) {
                $Individual->appendChild($dom->createElement("Okved", $this->okved));
            }
            if (!empty($this->okopf)) {
                $Individual->appendChild($dom->createElement("Okopf", $this->okopf));
            }
            if (!empty($this->phone)) {
                $Individual->appendChild($dom->createElement("ContactPhone", $this->phone));
            }
            $Individual->appendChild($dom->createElement("MspPerson", $this->msp));
            $Individual->appendChild($dom->createElement("OwnershipForm", $this->ownershipFormTypes[$this->ownershipform]));
            $Individual->appendChild($dom->createElement("OwnershipFormGrouping", $this->ownershipGroupTypes[$this->ownershipgroup]));

        }
        if ($this->registrationtype == 2) {
            //ЮЛ
            $client->appendChild($dom->createElement("RegistrationType", "Juridical"));
            $Juridical = $dom->createElement("Juridical");
            $Juridical->appendChild($dom->createElement("IdJuridical"));
            $client->appendChild($Juridical);

            $ClientFormJuridicalFinancialPosition = $dom->createElement("ClientFormJuridicalFinancialPosition");
            $ClientFormJuridicalFinancialPosition->appendChild($dom->createElement("ClientEvaluatesFinancialPosition","Missing"));
            $Juridical->appendChild($ClientFormJuridicalFinancialPosition);

            $Juridical->appendChild($dom->createElement("FullRegulationsName", $this->urname));
            $Juridical->appendChild($dom->createElement("ShortJuridicalName", $this->shorturname));

            if (!empty($this->govregistrationdate)) {
                $Juridical->appendChild($dom->createElement("GovRegistrationDate", $this->govregistrationdate));
            }
            if (!empty($this->govregistrationplace)) {
                $Juridical->appendChild($dom->createElement("GovRegistrationPlace", $this->govregistrationplace));
            }
            if (!empty($this->govregistrationathority)) {
                $Juridical->appendChild($dom->createElement("GovRegistrationAthority", $this->govregistrationathority));
            }

            $Juridical->appendChild($dom->createElement("Inn", $this->inn));

            $Juridical->appendChild($dom->createElement("OwnershipForm", $this->ownershipFormTypes[$this->ownershipform]));
            $Juridical->appendChild($dom->createElement("OwnershipFormGrouping", $this->ownershipGroupTypes[$this->ownershipgroup]));

            $Juridical->appendChild($dom->createElement("Kpp", $this->kpp));
            if (!empty($this->okpo)) {
                $Juridical->appendChild($dom->createElement("Okpo", $this->okpo));
            }
            if (!empty($this->okopf)) {
                $Juridical->appendChild($dom->createElement("Okopf", $this->okopf));
            }
            if (!empty($this->okved)) {
                $Juridical->appendChild($dom->createElement("Okved", $this->okved));
            }
            $Juridical->appendChild($dom->createElement("Ogrn", $this->ogrn));

            if ($this->uraddres) {
                $JuridicalAddress = $dom->createElement("JuridicalAddress");
                $JuridicalAddress->appendChild($dom->createElement("Fias", $this->uraddres->fias));
                $JuridicalAddress->appendChild($dom->createElement("Country", $this->uraddres->country));
                $JuridicalAddress->appendChild($dom->createElement("Region", $this->uraddres->region));
                $JuridicalAddress->appendChild($dom->createElement("AddressCity", $this->uraddres->city));
                $JuridicalAddress->appendChild($dom->createElement("AddressStreet", $this->uraddres->street));
                $JuridicalAddress->appendChild($dom->createElement("AddressHouse", $this->uraddres->house));
                if (!empty($this->uraddres->flat)) {
                    $JuridicalAddress->appendChild($dom->createElement("AddressFlat", $this->uraddres->flat));
                }
                $JuridicalAddress->appendChild($dom->createElement("FullAddress", $this->getFullAddress($this->uraddres)));
                $Juridical->appendChild($JuridicalAddress);
            }

            if ($this->actualaddres) {
                $ActualAddress = $dom->createElement("ActualAddress");
                $ActualAddress->appendChild($dom->createElement("Fias", $this->actualaddres->fias));
                $ActualAddress->appendChild($dom->createElement("Country", $this->actualaddres->country));
                $ActualAddress->appendChild($dom->createElement("Region", $this->actualaddres->region));
                $ActualAddress->appendChild($dom->createElement("AddressCity", $this->actualaddres->city));
                $ActualAddress->appendChild($dom->createElement("AddressStreet", $this->actualaddres->street));
                $ActualAddress->appendChild($dom->createElement("AddressHouse", $this->actualaddres->house));
                if (!empty($this->actualaddres->flat)) {
                    $ActualAddress->appendChild($dom->createElement("AddressFlat", $this->actualaddres->flat));
                }
                $ActualAddress->appendChild($dom->createElement("FullAddress", $this->getFullAddress($this->actualaddres)));
                $Juridical->appendChild($ActualAddress);
            }

            $GeneralDirector = $dom->createElement("GeneralDirector");
            $GeneralDirector->appendChild($dom->createElement("Surname", $this->lastname));
            $GeneralDirector->appendChild($dom->createElement("Name", $this->firstname));
            if (!empty($this->middlename)) {
                $GeneralDirector->appendChild($dom->createElement("Patronymic", $this->middlename));
            }
            if (!empty($this->dirregistrationdate)) {
                $GeneralDirector->appendChild($dom->createElement("RegistrationDate", $this->dirregistrationdate));
            }
            if (!empty($this->innfl)) {
                $GeneralDirector->appendChild($dom->createElement("Inn", $this->innfl));
            }
            if ($this->document) {
                $GeneralDirector->appendChild($dom->createElement("PassportSerial", $this->document->serial));
                $GeneralDirector->appendChild($dom->createElement("PassportNumber", $this->document->number));
                $GeneralDirector->appendChild($dom->createElement("PassportCode", $this->document->code));
                $GeneralDirector->appendChild($dom->createElement("PassportDate", $this->document->date));
                $GeneralDirector->appendChild($dom->createElement("PassportBy", $this->document->issueby));
            }
            $GeneralDirector->appendChild($dom->createElement("Resident", $this->resident));
            if ($this->document) {
                $GeneralDirector->appendChild($dom->createElement("ResidentDocument", $this->document->type == 1 ? 'passport' : 'otherdocument'));
            }
            $Juridical->appendChild($GeneralDirector);

            if (!empty($this->phone)) {
                $Juridical->appendChild($dom->createElement("ContactPhone", $this->phone));
            }
            if (!empty($this->email)) {
                $Juridical->appendChild($dom->createElement("Email", $this->email));
            }
            if (!empty($this->capitalpayed)) {
                $Juridical->appendChild($dom->createElement("CapitalPayed", $this->capitalpayed));
            }
            $Juridical->appendChild($dom->createElement("MspPerson", $this->msp));
            $Juridical->appendChild($dom->createElement("LegalFormsOfBusiness", $this->legalFormsOfBusinessTypes[$this->legalformsofbusiness]));
        }
        if ($this->registrationtype == 0) {
            //физлицо

            $client->appendChild($dom->createElement("RegistrationType", "PhysicalPerson"));
            $PhysicalPersonsRegistry = $dom->createElement("PhysicalPersonsRegistry");
            $client->appendChild($PhysicalPersonsRegistry);
            $ClientFormPhysicalPerson = $dom->createElement("ClientFormPhysicalPerson");
            $PhysicalPersonsRegistry->appendChild($ClientFormPhysicalPerson);

            $ClientFormPhysicalPerson->appendChild($dom->createElement("IdPerson"));

            $ClientFormPhysicalPerson->appendChild($dom->createElement("LastName", $this->lastname));
            $ClientFormPhysicalPerson->appendChild($dom->createElement("FirstName", $this->firstname));
            if (!empty($this->middlename)) {
                $ClientFormPhysicalPerson->appendChild($dom->createElement("MiddleName", $this->middlename));
            }
            if (!empty($this->birthdate)) {
                $ClientFormPhysicalPerson->appendChild($dom->createElement("BirthDate", $this->birthdate));
            }
            if (!empty($this->birthplace)) {
                $ClientFormPhysicalPerson->appendChild($dom->createElement("BirthPlace", $this->birthplace));
            }
            if (!empty($this->phone)) {
                $ClientFormPhysicalPerson->appendChild($dom->createElement("Phone", $this->phone));
            }
            $ClientFormPhysicalPerson->appendChild($dom->createElement("Gender",$this->gender ? 'Female' : 'Male'));
            $ClientFormPhysicalPerson->appendChild($dom->createElement("Inn", $this->innfl));
            $ClientFormPhysicalPerson->appendChild($dom->createElement("Nationality", $this->nationality));
            $ClientFormPhysicalPerson->appendChild($dom->createElement("PDL", $this->pdl));
            $ClientFormPhysicalPerson->appendChild($dom->createElement("RPDL", $this->rpdl));

            if ($this->document) {
                $IdentityDocument = $dom->createElement("IdentityDocument");
                $IdentityDocument->appendChild($dom->createElement("SerialNumber", $this->document->serial));
                $IdentityDocument->appendChild($dom->createElement("Number", $this->document->number));
                $IdentityDocument->appendChild($dom->createElement("IssueBy", $this->document->issueby));
                $IdentityDocument->appendChild($dom->createElement("Date", $this->document->date));
                $IdentityDocument->appendChild($dom->createElement("Code", $this->document->code));
                $ClientFormPhysicalPerson->appendChild($IdentityDocument);
                $ClientFormPhysicalPerson->appendChild($dom->createElement("IdentityDocumentType", $this->docTypes[$this->document->type]));
            }

            if ($this->righttostaydocument) {
                $ClientFormPhysicalPerson->appendChild($dom->createElement("ConfirmingRightToStayDocumentType", $this->docTypes[$this->righttostaydocument->type]));
                $ConfirmingRightToStayDocument = $dom->createElement("ConfirmingRightToStayDocument");
                $ConfirmingRightToStayDocument->appendChild($dom->createElement("SerialNumber", $this->righttostaydocument->serial));
                $ConfirmingRightToStayDocument->appendChild($dom->createElement("Number", $this->righttostaydocument->number));
                $ConfirmingRightToStayDocument->appendChild($dom->createElement("IssueBy", $this->righttostaydocument->issueby));
                $ConfirmingRightToStayDocument->appendChild($dom->createElement("StartDate", $this->righttostaydocument->date));
                $ConfirmingRightToStayDocument->appendChild($dom->createElement("FinishDate", $this->righttostaydocument->date));
                $ClientFormPhysicalPerson->appendChild($ConfirmingRightToStayDocument);
            } else {
                $ConfirmingRightToStayDocumentType = $dom->createElement("ConfirmingRightToStayDocumentType");
                $ConfirmingRightToStayDocumentType->setAttribute('xsi:nil', 'true');
                $ClientFormPhysicalPerson->appendChild($ConfirmingRightToStayDocumentType);
                $ConfirmingRightToStayDocument = $dom->createElement("ConfirmingRightToStayDocument");
                $StartDate = $dom->createElement("StartDate");
                $StartDate->setAttribute('xsi:nil', 'true');
                $ConfirmingRightToStayDocument->appendChild($StartDate);
                $FinishDate = $dom->createElement("FinishDate");
                $FinishDate->setAttribute('xsi:nil', 'true');
                $ConfirmingRightToStayDocument->appendChild($FinishDate);
                $ClientFormPhysicalPerson->appendChild($ConfirmingRightToStayDocument);
            }

            if ($this->actualaddres) {
                $FactAddress = $dom->createElement("FactAddress");
                $FactAddress->appendChild($dom->createElement("Fias", $this->actualaddres->fias));
                $FactAddress->appendChild($dom->createElement("Country", $this->actualaddres->country));
                $FactAddress->appendChild($dom->createElement("Region", $this->actualaddres->region));
                $FactAddress->appendChild($dom->createElement("AddressCity", $this->actualaddres->city));
                $FactAddress->appendChild($dom->createElement("AddressStreet", $this->actualaddres->street));
                $FactAddress->appendChild($dom->createElement("AddressHouse", $this->actualaddres->house));
                if (!empty($this->actualaddres->flat)) {
                    $FactAddress->appendChild($dom->createElement("AddressFlat", $this->actualaddres->flat));
                }
                $FactAddress->appendChild($dom->createElement("FullAddress", $this->getFullAddress($this->actualaddres)));
                $ClientFormPhysicalPerson->appendChild($FactAddress);
            }
            if ($this->uraddres) {
                $RegistrationAddress = $dom->createElement("RegistrationAddress");
                $RegistrationAddress->appendChild($dom->createElement("Fias", $this->uraddres->fias));
                $RegistrationAddress->appendChild($dom->createElement("Country", $this->uraddres->country));
                $RegistrationAddress->appendChild($dom->createElement("Region", $this->uraddres->region));
                $RegistrationAddress->appendChild($dom->createElement("AddressCity", $this->uraddres->city));
                $RegistrationAddress->appendChild($dom->createElement("AddressStreet", $this->uraddres->street));
                $RegistrationAddress->appendChild($dom->createElement("AddressHouse", $this->uraddres->house));
                if (!empty($this->uraddres->flat)) {
                    $RegistrationAddress->appendChild($dom->createElement("AddressFlat", $this->uraddres->flat));
                }
                $RegistrationAddress->appendChild($dom->createElement("FullAddress", $this->getFullAddress($this->uraddres)));
                $ClientFormPhysicalPerson->appendChild($RegistrationAddress);
            }
        }

        $beneficiary = $dom->createElement("beneficiary");
        $beneficiary->appendChild($dom->createElement("accountNumber", $partnerBankGate->SchetNumber));
        $beneficiary->appendChild($dom->createElement("groundForParticipation", $this->groundforparticipation));
        $beneficiary->appendChild($dom->createElement("participateInContract",true));
        $beneficiary->appendChild($dom->createElement("provisionOfInformation",true));
        $beneficiary->appendChild($dom->createElement("modifOrTermWithoutConsent",true));
        $beneficiary->appendChild($dom->createElement("notifyUponTermination",true));
        $beneficiary->appendChild($dom->createElement("joiningDate", $this->joiningdate));
        //$beneficiary->appendChild($dom->createElement("disposalDate"));
        $Request->appendChild($beneficiary);

        //die($dom->saveXML($Envelope));
        return $dom->saveXML($Envelope);
    }

    private function getFullAddress($address)
    {
        $adr = $address->region . ' ' . $address->regiontype;
        if (!empty($address->city)) {
            $adr .= ', ' . $address->citytype . ' ' . $address->city;
        }
        if (!empty($address->place)) {
            $adr .= ', ' . $address->placetype . ' ' . $address->place;
        }
        if (!empty($address->street)) {
            $adr .= ', ' . $address->streettype . ' ' . $address->street;
        }
        if (!empty($address->house)) {
            $adr .= ', д ' . $address->house;
        }
        if (!empty($address->corp)) {
            $adr .= ' ' . $address->corp;
        }
        if (!empty($address->flat)) {
            $adr .= ', ' . $address->flat;
        }

        return $adr;
    }

    /**
     * @return array
     */
    public function ParseResult()
    {
        $ret = ['error' => 999, 'id' => 0, 'message' => ''];
        $dom = new \DOMDocument();
        try {
            $dom->loadXML($this->result);
        } catch (\Exception $e) {
            return $ret;
        }
        /** @var \DOMNode $resp */
        $resp = @$dom->getElementsByTagName('SetBeneficiaryResponse')[0];
        $faultcode = @$dom->getElementsByTagName('faultstring')[0];
        if ($resp) {
            /** @var \DOMNode $node */
            foreach ($resp->childNodes as $node) {
                if ($node->nodeName == 'errCode') {
                    $ret['error'] = $node->nodeValue;
                }
                if ($node->nodeName == 'errMsg') {
                    $ret['message'] = $node->nodeValue;
                }
                if ($node->nodeName == 'beneficiaryId') {
                    $ret['id'] = $node->nodeValue;
                }
            }
        } elseif ($faultcode) {
            $ret['error'] = 500;
            $ret['id'] = 0;
            $ret['message'] = $faultcode->nodeValue;
        }

        return $ret;
    }

    /**
     * @param $Req
     * @return bool|string|null
     */
    public function EncodeFile($Req)
    {
        $data = base64_decode($Req);
        $dom = new \DOMDocument();
        try {
            $dom->loadXML($data);

            $xpath = new \DOMXPath($dom);
            /** @var DOMNodeList $res */
            $res = $xpath->query('//*[local-name(.) = "Envelope"]/*[local-name(.) = "Body"]/*[local-name(.) = "SetBeneficiaryRequest"]/client/Juridical');
            if (!$res || !$res->length) {
                $res = $xpath->query('//*[local-name(.) = "Envelope"]/*[local-name(.) = "Body"]/*[local-name(.) = "SetBeneficiaryRequest"]/client/Individual');
            }
            if ($res && $res->length) {
                return $data;
            }
        } catch (\yii\base\ErrorException $e) {
        }
        return null;
    }

}
