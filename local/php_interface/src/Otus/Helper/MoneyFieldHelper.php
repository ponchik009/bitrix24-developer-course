<?

namespace Otus\Helper;

class MoneyFieldHelper {
	/**
	 * Получает представление денег из базы (например, 30|RUB)
	 * Возвращает числовое представление
 	*/
	public static function getMoneyNumber($moneyValue) {
		return explode("|", $moneyValue)[0];
	}
}