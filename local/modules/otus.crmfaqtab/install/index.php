<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

use Bitrix\Main\Entity\Base;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\DB\SqlQueryException;

use Otus\Crmfaqtab\Orm\FaqTable;
use Otus\Crmfaqtab\Data\TestDataInstaller;

class otus_crmfaqtab extends CModule
{
    public $MODULE_ID = 'otus.crmfaqtab';
    public $MODULE_SORT = 500;
    public $MODULE_VERSION;
    public $MODULE_DESCRIPTION;
    public $MODULE_VERSION_DATE;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_DESCRIPTION = 'Модуль добавляет в карточни выбранных сущностей CRM новый таб - FAQ. В нем можно разместить вспомогательные материалы, чтобы помочь пользователям в работе с системой.';
        $this->MODULE_NAME = 'FAQ таб в CRM сущностях';
        $this->PARTNER_NAME = 'Образовательная платформа Otus';
        $this->PARTNER_URI = 'https://otus.ru/';
    }

    /**
     * @throws SystemException
     */
    public function DoInstall(): void
    {
        if ($this->isVersionD7()) {
        	$context = Application::getInstance()->getContext();
        	$request = $context->getRequest();
        	
        	global $APPLICATION;
        	
        	// Установка в несколько шагов
        	// сурс: https://hmarketing.ru/blog/bitrix/struktura-modulya/
        	if ($request["step"] < 2) {
	            // подключаем скрипт с административным прологом и эпилогом
	            $APPLICATION->IncludeAdminFile(
	                'Настройки установки модуля',
	                __DIR__ . '/step1.php'
	            );
	        }
	        
	        if ($request["step"] == 2) {
	            ModuleManager::registerModule($this->MODULE_ID);
            
	            $this->InstallFiles();
	            $this->InstallDB();
	            $this->InstallEvents();
	           
	            // проверяим ответ формы введеный пользователем на первом шаге
	            if ($request["add_data"] == "Y") {
	                TestDataInstaller::addQuestions();
	            }
	            
	            // подключаем скрипт с административным прологом и эпилогом
	            $APPLICATION->IncludeAdminFile(
	                'Завершение установки',
	                __DIR__ . '/step2.php'
	            );
	        }
        } else {
            throw new SystemException('Для корректной работы модуля версия Битрикса должна быть >= 20.00.00. Пожалуйста, обновите Битрикс до актуальной версии.');
        }
    }

    /**
     * @throws SqlQueryException
     * @throws LoaderException
     * @throws InvalidPathException
     */
    public function DoUninstall(): void
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * @throws InvalidPathException
     */
    public function InstallFiles($params = []): void
    {
        $component_path = $this->getPath() . '/install/components';

        if (Directory::isDirectoryExists($component_path)) {
            CopyDirFiles($component_path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        } else {
            throw new InvalidPathException($component_path);
        }
    }

    /**
     * @throws LoaderException
     */
    public function InstallDB(): void
    {
        Loader::includeModule($this->MODULE_ID);

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (!Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                Base::getInstance($entity)->createDbTable();
            }
        }
    }

    public function InstallEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Crmfaqtab\\Crm\\Handlers',
            'updateTabs'
        );
    }

    public function UnInstallEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Crmfaqtab\\Crm\\Handlers',
            'updateTabs'
        );
    }

    /**
     * @throws SqlQueryException
     * @throws LoaderException
     */
    public function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        $connection = Application::getConnection();

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                $connection->dropTable($entity::getTableName());
            }
        }
    }

    /**
     * Удаляет файлы, установленные компонентом
     * @throws InvalidPathException
     */
    public function UninstallFiles(): void
    {
        $component_path = $this->getPath() . '/install/components';

        if (Directory::isDirectoryExists($component_path)) {
            $installed_components = new \DirectoryIterator($component_path);
            foreach ($installed_components as $component) {
                if ($component->isDir() && !$component->isDot()) {
                    $target_path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $component->getFilename();
                    if (Directory::isDirectoryExists($target_path)) {
                        Directory::deleteDirectory($target_path);
                    }
                }
            }
        } else {
            throw new InvalidPathException($component_path);
        }
    }


    private function getEntities(): array
    {
        return [
            FaqTable::class,
        ];
    }

    public function getPath($notDocumentRoot = false): string
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function isVersionD7(): bool
    {
        return version_compare(ModuleManager::getVersion('main'), '20.00.00', ">");
    }
}