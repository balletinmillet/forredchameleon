<?

namespace Quantum;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\DiscountCouponsManager;
Loader::includeModule('sale');

class Event
{
	public static function onPageStart()
	{
		self::setupEventHandlers();
	}

	/**
	 * Добавляет обработчики событий
	 *
	 * @return void
	 */
	protected static function setupEventHandlers()
	{

    }
}
