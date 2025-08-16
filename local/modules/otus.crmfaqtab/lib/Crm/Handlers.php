<?php

namespace Otus\Crmfaqtab\Crm;

use Otus\Crmfaqtab\Orm\FaqTable;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Handlers
{
    public static function updateTabs(Event $event): EventResult
    {
        $availableEntityIds = Option::get('otus.crmfaqtab', 'ENTITIES_TO_DISPLAY_TAB');
        $availableEntityIds = explode(',', $availableEntityIds);
        
        $entityTypeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');
        
        $tabs = $event->getParameter('tabs');
        if (in_array($entityTypeId, $availableEntityIds)) {
            $tabs[] = [
                'id' => 'book_tab_' . $entityTypeId . '_' . $entityId,
                'name' => 'FAQ',
                'enabled' => true,
                'loader' => [
                    'serviceUrl' => sprintf(
                        '/bitrix/components/otus.crmfaqtab/faq.grid/lazyload.ajax.php?site=%s&%s',
                        \SITE_ID,
                        \bitrix_sessid_get(),
                    ),
                    'componentData' => [
                        'template' => '',
                        'params' => [
                            'ORM' => FaqTable::class,
                            'DEAL_ID' => $entityId,
                        ],
                    ],
                ],
            ];
        }

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs,]);
    }
}