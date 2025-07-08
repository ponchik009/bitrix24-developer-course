<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/** @global $APPLICATION */
$APPLICATION->SetTitle('Пациенты');

use Otus\Orm\PatientTable;
use Otus\Orm\PatientCardTable;

use Bitrix\Main\Entity\Query;

$patientsResult = PatientTable::getList([
	'select' => [
		'ID',
		'FIRST_NAME',
		'LAST_NAME',
		'SECOND_NAME',
		
		'PATIENT_CARD_ID' => 'PATIENT_CARD.ID',
		'PATIENT_CARD_CREATED_AT' => 'PATIENT_CARD.CREATED_AT',
		
		// почему-то не получается одновременно вытянуть доверителей и доверенного
		// 'TRUSTEE_ID',
		// 'TRUSTEE_NAME' => 'TRUSTEE.FIRST_NAME',
		// 'TRUSTEE_LAST_NAME' => 'TRUSTEE.LAST_NAME',
		// 'TRUSTEE_SECOND_NAME' => 'TRUSTEE.SECOND_NAME',
		
		'PRINCIPALS_ID' => 'PRINCIPALS.ID',
		'PRINCIPALS_NAME' => 'PRINCIPALS.FIRST_NAME',
		'PRINCIPALS_LAST_NAME' => 'PRINCIPALS.LAST_NAME',
		'PRINCIPALS_SECOND_NAME' => 'PRINCIPALS.SECOND_NAME',
		
		'FAVORITE_DOCTORS_ID' => 'FAVORITE_DOCTORS.ID',
		'FAVORITE_DOCTORS_NAME' => 'FAVORITE_DOCTORS.NAME',
	]
])->fetchAll();

$patients = [];
foreach($patientsResult as $patient) {
	if (empty($patients[$patient['ID']])) {
		$patients[$patient['ID']] = [
			'FIRST_NAME' => $patient['FIRST_NAME'],
			'LAST_NAME' => $patient['LAST_NAME'],
			'SECOND_NAME' => $patient['SECOND_NAME'],
			'CARD' => [
				'ID' => $patient['PATIENT_CARD_ID'],
				'CREATED_AT' => $patient['PATIENT_CARD_CREATED_AT'],
			],
			'PRINCIPALS' => [],
			'FAVORITE_DOCTORS' => [],
		];
	}
	
	if (!empty($patient['PRINCIPALS_ID'])) {
		$patients[$patient['ID']]['PRINCIPALS'][$patient['PRINCIPALS_ID']] = [
			'FIRST_NAME' => $patient['PRINCIPALS_NAME'],
			'LAST_NAME' => $patient['PRINCIPALS_LAST_NAME'],
			'SECOND_NAME' => $patient['PRINCIPALS_SECOND_NAME'],
		];
	}
	
	if (!empty($patient['FAVORITE_DOCTORS_ID'])) {
		$patients[$patient['ID']]['FAVORITE_DOCTORS'][$patient['FAVORITE_DOCTORS_ID']] = [
			'NAME' => $patient['FAVORITE_DOCTORS_NAME'],
		];
	}
}

?>

<? if (!empty($patients)): ?>
	<table border='1'>
		<thead>
			<tr>
				<td>Пациент</td>
				<td>Карта</td>
				<td>Доверители</td>
				<td>Любимые доктора</td>
			</tr>
		</thead>
		<tbody>
			<? foreach($patients as $patient): ?>
				<tr>
					<td><?= sprintf('%s %s %s', $patient['LAST_NAME'], $patient['FIRST_NAME'], $patient['SECOND_NAME']) ?></td>
					<td><?= sprintf('%s | %s', $patient['CARD']['ID'], $patient['CARD']['CREATED_AT']->format('d.m.Y')) ?></td>
					<td><?= implode('; ', array_map(fn($item) => sprintf('%s %s %s', $item['LAST_NAME'], $item['FIRST_NAME'], $item['SECOND_NAME']), $patient['PRINCIPALS'])) ?></td>
					<td><?= implode('; ', array_map(fn($item) => $item['NAME'], $patient['FAVORITE_DOCTORS'])) ?></td>
				</tr>
			<? endforeach; ?>
		</tbody>
	</table>
<? endif; ?>