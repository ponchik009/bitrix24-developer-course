<?

namespace Otus\Rest;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RestEventsRegister {
    /**
     * Регистрирует REST методы
     * 
     * Чтобы методы можно было выбрать в настройке прав,
     * необходимо очистить кеш
     * Bitrix\Main\Data\Cache::clearCache(true, '/rest/scope/');
     * @return array[]
     */
    public static function OnRestServiceBuildDescriptionHandler()
    {
        Loc::getMessage('REST_SCOPE_OTUS.PATIENT');

        return [
            'otus.patient' => [
                'otus.patient.add' => ['Otus\Rest\PatientController', 'add'],
                'otus.patient.update' => ['Otus\Rest\PatientController', 'update'],
                'otus.patient.remove' => ['Otus\Rest\PatientController', 'remove'],
                'otus.patient.getList' => ['Otus\Rest\PatientController', 'getList'],
                'otus.patient.getById' => ['Otus\Rest\PatientController', 'getById'],
            ],
        ];
    }
}