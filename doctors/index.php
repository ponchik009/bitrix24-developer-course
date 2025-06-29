<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/** @global $APPLICATION */
$APPLICATION->SetTitle('Врачи');
$APPLICATION->SetAdditionalCSS('/doctors/styles.css');

use \Models\Lists\DoctorsPropertyValuesTable as DoctorsTable;
use \Models\Lists\ProceduresPropertyValuesTable as ProceduresTable;

use Bitrix\Main\Entity;

// получение списка докторов
function getDoctorsList() {
	$doctors = DoctorsTable::getList([
		'select' => [
			'ID' => 'IBLOCK_ELEMENT_ID',
			'NAME' => 'ELEMENT.NAME',
		],
	])->fetchAll();
	
	return $doctors;
}

// получение доктора по id
function getDoctorById($id) {
	$doctor = DoctorsTable::query()
		->setSelect([
			'ID' => 'ELEMENT.ID',
			'NAME' => 'ELEMENT.NAME',
			'PROCEDURE_IDS'
		])
		->where('ID', $id)
		->fetch();
	
	return $doctor;
}

// получение списка процедур
function getProcedures($ids = null) {
	$filter = [];
	
	if ($ids) {
		$filter['ID'] = $ids;
	}
	
	$procedures = ProceduresTable::getList([
		'select' => [
			'ID' => 'IBLOCK_ELEMENT_ID',
			'NAME' => 'ELEMENT.NAME'
		],
		'filter' => $filter
	])->fetchAll();
	
	return $procedures;
}

// создание доктора
function addDoctor($data) {
	return DoctorsTable::add($data);
}

// изменение доктора
function editDoctor($id, $data) {
	return DoctorsTable::update($id, $data);
}

// создание процедуры
function addProcedure($data) {
	return ProceduresTable::add($data);
}

// изменение процедуры
function editProcedure($id, $data) {
	return ProceduresTable::update($id, $data);
}

// определение action страницы
function resolveAction($items) {
	if ($items[0] === "" || count($items) === 0) {
		// список докторов
		return "doctor.list";
	}
	
	if (count($items) === 1 && $items[0] === "add") {
		// добавление доктора
		return "doctor.add";
	}
	
	if (count($items) === 2 && $items[1] === "edit") {
		// просмотр, редактирование доктора
		return "doctor.edit";
	}
	
	if (count($items) == 1 && $items[0] === "procedures") {
		// список процедур
		return "procedure.list";
	}
	
	if (count($items) == 2 && $items[1] == "add") {
		// добавление процедуры
		return "procedure.add";
	}
	
	if (count($items) == 3 && $items[2] == "edit") {
		// просмотр, редактирование процедуры
		return "procedure.edit";
	}
	
	return "unknown";
}

$path = trim($_GET['path']);
$pathArray = explode("/", $path);
$action = resolveAction($pathArray);
$method = $_SERVER['REQUEST_METHOD'];

vd($action);

?>

<nav class="main-menu">
	<a class="nav-menu-link" href="/doctors">Доктора</a>
	<a class="nav-menu-link" href="/doctors/procedures">Процедуры</a>
</nav>

<? 

if ($action === "doctor.list") {
	$APPLICATION->SetTitle('Врачи');
	
	$doctors = getDoctorsList();
	
	echo '<a class="nav-menu-link" href="/doctors/add">Добавить врача</a>';
	
	echo '<div class="items-list">';
	
	foreach($doctors as $doctor) {
?>
	<a class="items-list__card" href="/doctors/<?=$doctor["ID"]?>/edit">
		<?= $doctor["NAME"] ?>
	</a>
<?
	}
	
	echo '</div>';
}

?>

<? 

if ($action === "doctor.add") {
	$APPLICATION->SetTitle('Добавить врача');
	
	if ($method == "POST") {
		addDoctor($_POST);
		header("Location: /doctors");
		exit();
	}
	
	$procedures = getProcedures();
?>
	<form action="/doctors/add" method="POST">
		<input class="input" name="NAME" placeholder="ФИО" />
		<? foreach($procedures as $procedure): ?>
			<label>
				<?= $procedure["NAME"] ?>
				<input type="checkbox" name="PROCEDURE_IDS[]" value="<?= $procedure["ID"] ?>" />
			</label>
		<? endforeach; ?>
		<button class="button" type="submit">Создать</button>
	</form>
<?
	
}

?>

<? 

if ($action === "doctor.edit") {
	$doctorId = $pathArray[0];
	
	if ($method == "POST") {
		$result = editDoctor($doctorId, $_POST);
		header("Location: /doctors");
		exit();
	}
	
	$doctor = getDoctorById($doctorId);
	$procedures = getProcedures();
	
	$APPLICATION->SetTitle("Врач {$doctor["NAME"]}");
?>
	<form action="/doctors/<?=$doctorId?>/edit" method="POST">
		<input class="input" name="NAME" placeholder="ФИО" value="<?=$doctor["NAME"]?>" />
		<? foreach($procedures as $procedure): ?>
			<label>
				<?= $procedure["NAME"] ?>
				<input type="checkbox" name="PROCEDURE_IDS[]" value="<?= $procedure["ID"] ?>" <? if (in_array($procedure["ID"], $doctor["PROCEDURE_IDS"])) echo "checked"; ?>  />
			</label>
		<? endforeach; ?>
		<button class="button" type="submit">Изменить</button>
	</form>
<?
	
}

?>

<? 

if ($action === "procedure.list") {
	$APPLICATION->SetTitle("Процедуры");
	
	$procedures = getProcedures();
	
	echo '<a class="nav-menu-link" href="/doctors/procedures/add">Добавить процедуру</a>';
	
	echo '<div class="items-list">';
	
	foreach($procedures as $procedure) {
?>
	<a class="items-list__card" href="/doctors/procedures/<?=$procedure["ID"]?>/edit">
	 	<?= $procedure["NAME"] ?>
	</a>
<?

	}
	
	echo '</div>';
}

?>

<? 

if ($action === "procedure.add") {
	$APPLICATION->SetTitle("Добавление процедуры");
	
	if ($method == "POST") {
		addProcedure($_POST);
		header("Location: /doctors/procedures");
		exit();
	}
	
?>
	<form action="/doctors/procedures/add" method="POST">
		<input class="input" name="NAME" placeholder="Название" />
		<button class="button" type="submit">Создать</button>
	</form>
<?
	
}

?>

<? 

if ($action === "procedure.edit") {
	$procedureId = $pathArray[1];
	
	if ($method == "POST") {
		$result = editProcedure($procedureId, $_POST);
		header("Location: /doctors/procedures");
		exit();
	}
	
	$procedure = getProcedures($procedureId)[0];
	
	$APPLICATION->SetTitle("Процедура {$procedure["NAME"]}");
?>
	<form action="/doctors/procedures/<?=$procedureId?>/edit" method="POST">
		<input class="input" name="NAME" placeholder="Название" value="<?=$procedure["NAME"]?>" />
		<button class="button" type="submit">Изменить</button>
	</form>
<?
	
}

?>

