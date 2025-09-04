<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\ErrorCollection;
use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Crm\Service;

\Bitrix\Main\Loader::includeModule('crm');

class CBPGetCustomerByInnActivity extends BaseActivity {
    /**
     * @see parent::_construct()
     * @param $name string Activity name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }
    
    /**
     * Return activity file path
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }
    
    /**
     * Основной метод, вызываемый при выполнении активити
     * 
     * @return ErrorCollection
     */
    protected function internalExecute(): ErrorCollection 
    {
        $errors = parent::internalExecute();

        // в рабочем активити необходимо будет создать отдельный метод который будет получать результат ответа сервиса Dadata, 
        // обходить циклом результат и сохранять в массив все полученные организации
        //$this->preparedProperties['Text'] = $companyName;
        //$this->log($this->preparedProperties['Text']);

        $rootActivity = $this->GetRootActivity(); // получаем объект активити
        // сохранение полученных результатов работы активити в переменную бизнес процесса
        // $rootActivity->SetVariable("TEST", $this->preparedProperties['Text']); 

        // получение значения полей документа в активити        
        $documentType = $rootActivity->getDocumentType(); // получаем тип документа
        $documentId = $rootActivity->getDocumentId(); // получаем ID документа 

        // получаем объект документа над которым выполняется БП (элемент сущности Компания)
        $documentService = $this->workflow->GetService("DocumentService");   

        // поля документа
        $documentFields =  $documentService->GetDocumentFields($documentType);

        foreach ($documentFields as $key => $value) {
        	if ($key == "PROPERTY_CUSTOMER_INN") {
	        	$inn = $documentService->getFieldValue($documentId, $key, $documentType);
	        	$this->log('ИНН Компании: ' . $key . ' - ' . $inn);
	        	
	        	$existedCompany = $this->getCompanyByINN($inn);
	        	
	        	if (!empty($existedCompany)) {
	        		$this->preparedProperties['CompanyId'] = $existedCompany->getId();
	        		$this->log('ID Компании: ' . $this->preparedProperties['CompanyId']);
	        	} else {
	        		$companyFromDadata = $this->fetchCompanyByINN($inn);
	        		
	        		if (empty($companyFromDadata)) {
	        			$this->log("Не удалось найти компанию по ИНН " . $inn);
	        			$errors[] = new Bitrix\Main\Error("Не удалось найти компанию по ИНН " . $inn);
	        			
	        			break;
	        		}
	        		
	        		$entityFields = [
					    // Основные поля
					    'TITLE' => $companyFromDadata['data']['name']['short_with_opf'],
					    'COMPANY_TYPE' => 'CUSTOMER',
					    
					    'UF_COMPANY_INN' => $companyFromDadata['data']['inn'],
					
					    // Технические поля
					    "OPENED" => "Y", // "Доступен для всех" = Да
					    "ASSIGNED_BY_ID" => 1, // По-умолчанию ответственным будет пользователь с ID:123
					];
					
					$entityObject = new \CCrmCompany(false);
					
					$companyId = $entityObject->Add($entityFields);
					
					if (!empty($companyId)) {
						$this->preparedProperties['CompanyId'] = $companyId;
	        			$this->log('ID Компании: ' . $this->preparedProperties['CompanyId']);
					} else {
						$this->log("Не удалось создать компанию. Текст ошибки: " . $entityObject->LAST_ERROR);
						$errors[] = new Bitrix\Main\Error("Не удалось создать компанию. Текст ошибки: " . $entityObject->LAST_ERROR);
					}
	        	}
	        	
	        	break;
        	}
        }
        

        return $errors;
    }
    
    /**
     * Метод для получения компании внутри системы
     */
    protected function getCompanyByINN(string $inn) {
    	$container = Service\Container::getInstance();
    	$factory = $container->getFactory(\CCrmOwnerType::Company);
    	$company = $factory->getItems([
		    'filter' => [
		        '=UF_COMPANY_INN' => $inn
		    ],
		    'limit' => 1,
		])[0];
		
		return $company;
    }
    
    /**
     * Метод для получения данных о компании по ИНН
     */
    protected function fetchCompanyByINN(string $inn) {
		$token = $_ENV["DADATA_API_KEY"];
		$secret = $_ENV["DADATA_SECRET_KEY"];
		$dadata = new \Dadata\DadataClient($token, $secret);

        $result = $dadata->findById("party", $inn, 1);
        
        return $result[0];
    }
}