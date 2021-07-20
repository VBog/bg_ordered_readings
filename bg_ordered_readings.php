<?php
/*======================================================================================================
					ФУНКЦИИ ДЛЯ РАСЧЕТА РЯДОВЫХ ЧТЕНИЙ
					
	Версия 2.0

		bg_Gospel_at_Liturgy ($date) 	- чтение Евангелие на Литургии
		bg_Apostol_at_Liturgy ($date) 	- чтение Апостола на Литургии
		bg_Gospel_at_Matins ($date) 	- чтение Евангелие на Утрене
	Параметры:
		$date - дата по новому стилю в формате Y-m-d
				
=========================================================================================================*/
class OrderedReadings {
	
/*******************************************************************************
	Функция определяет день Пасхи или переходящий праздник в указанном году
	Параметры:
		$year - год в формате Y
		$shift - смещение даты относительно Пасхи (переходящий праздник)
				по умолчанию $shift=0 - день Пасхи
		$old = true - возвращает дату по старому стилю, false - по новому стилю
	Возвращает:
		Дату Пасхи или переходящий праздник в формате Y-m-d	
*******************************************************************************/
	public function bg_get_easter($year, $shift=0, $old=false) {
		$a=((19*($year%19)+15)%30);
		$b=((2*($year%4)+4*($year%7)+6*$a+6)%7);
		if ($a+$b>9) {
			$day=$a+$b-9;
			$month=4;
		} else {
			$day=22+$a+$b;
			$month=3;
		}
		if ($old) $dd = 0;
		else $dd = $this->bg_ddif($year);
		return date( 'Y-m-d', mktime ( 0, 0, 0, $month, $day+$dd+$shift, $year ) );
	}
/*******************************************************************************
	Функция возвращает количество дней между датами по новому и старому стилю
	Параметры:
		$year - год в формате Y
	Возвращает:
		Количество дней между датами по новому и старому стилю	
*******************************************************************************/  
	public function bg_ddif($year) {
		return ($year-$year%100)/100 - ($year-$year%400)/400 - 2;
	}

/*******************************************************************************
	Функция возвращает количество дней между Пасхой и указанной датой по новому стилю
	Параметры:
		$date - дата в формате Y-m-d
		$prev = true - Пасха предыдущего года, false - Пасха текущего года
	Возвращает:
		Количество дней между Пасхой и указанной датой	
*******************************************************************************/  
	public function bg_date_easter_dif($date, $year) {
		$interval = date_diff(date_create($this->bg_get_easter($year)), date_create($date));
		return (int)$interval->format('%R%a');
	}
/*******************************************************************************
	Функция определяет дату Конца годового круга
		$year - год в формате Y
		$old = true - возвращает дату по старому стилю, false - по новому стилю
	Возвращает:
		Дату Конца годового круга в формате Y-m-d	
*******************************************************************************/
	private function bg_get_end_of_cycle ($year, $old=false) {
		$sunday_after_exaltation = $this->bg_get_sunday_after_exaltation($year-1, true);	// Неделя по Воздвижении прошлого года по ст.ст.
		list ($y, $m, $d) = explode ('-',$sunday_after_exaltation);

		if ($old) $dd = 0;
		else $dd = $this->bg_ddif($year-1);
		return date( 'Y-m-d', mktime ( 0, 0, 0, $m, $d+112+$dd, $y ) );
	}
/*******************************************************************************
	Функция определяет дату Недели по Воздвижению (14.09 по ст.ст.)
		$year - год в формате Y
		$old = true - возвращает дату по старому стилю, false - по новому стилю
	Возвращает:
		Дату Недели по Воздвижению в формате Y-m-d	
*******************************************************************************/
	private function bg_get_sunday_after_exaltation($year, $old=false) {
		$exaltation = (int) date( 'N', mktime ( 0, 0, 0, 9, 14+$this->bg_ddif($year), $year ) );	// День недели Воздвижения от 1 до 7
		$diff = 7-$exaltation; 			// Осталось до Недели по Воздвижению
		if ($diff == 0) $diff = 7;		// Если Воздвижение в воскресенье, то через 7 дней
		
		if ($old) $dd = 0;
		else $dd = $this->bg_ddif($year);
		return date( 'Y-m-d', mktime ( 0, 0, 0, 9, 14+$diff+$dd, $year ) );
	}
/*******************************************************************************
	Функция определяет дату Недели Святых Праотец (За 2 Недели до Рождества Христова 25.12 по ст.ст.)
		$year - год в формате Y
		$old = true - возвращает дату по старому стилю, false - по новому стилю
	Возвращает:
		Дату Недели Святых Праотец в формате Y-m-d	
*******************************************************************************/
	private function bg_get_sunday_holy_forefathers($year, $old=false) {
		$christmas = (int) date( 'N', mktime ( 0, 0, 0, 12, 25+$this->bg_ddif($year-1), $year ) );	// День недели Рождества от 1 до 7
		$diff = -$christmas; 			// Прошло от Недели перед Рождеством, Святых Отец
		if ($diff == 0) $diff = -7;		// Если Рождество в воскресенье, то на 7 дней ранее
		$diff -= 7;						// Неделя Святых Праотец предшествует Неделе Святых Отец
		
		if ($old) $dd = 0;
		else $dd = $this->bg_ddif($year-1);
		return date( 'Y-m-d', mktime ( 0, 0, 0, 12, 25+$diff+$dd, $year ) );
	}

/*========================================================================================
	Чтения Евангелие на Литургии
	Параметры:
		$date - дата в формате Y-m-d
	Возвращает:
		Текст, содержащий список ссылок на Евангелие
 ========================================================================================*/
	public function bg_Gospel_at_Liturgy ($date) {
		$gl = [	'-104'=>'Мк.8:11-21','-103'=>'Мк.8:22-26','-102'=>'Мк.8:30-34','-101'=>'Мк.9:10-16','-100'=>'Мк.9:33-41','-99'=>'Лк.14:1-11','-98'=>'Лк.18:18-27',		// Повтор 30 по Пятидесятнице ****
				'-97'=>'Мк.9:42-10:1','-96'=>'Мк.10:2-12','-95'=>'Мк.10:11-16','-94'=>'Мк.10:17-27','-93'=>'Мк.10:23-32','-92'=>'Лк.16:10-15','-91'=>'Лк.18:35-43',		// Повтор 31 по Пятидесятнице ***
				'-90'=>'Мк.10:46-52','-89'=>'Мк.11:11-23','-88'=>'Мк.11:23-26','-87'=>'Мк.11:27-33','-86'=>'Мк.12:1-12','-85'=>'Мф.25:1-13','-84'=>'Мф.15:21-28',		// Повтор 17 по Пятидесятнице *****
				'-83'=>'Мк.10:46-52','-82'=>'Мк.11:11-23','-81'=>'Мк.11:23-26','-80'=>'Мк.11:27-33','-79'=>'Мк.12:1-12','-78'=>'Лк.17:3-10','-77'=>'Лк.19:1-10',		// О Закхее: повтор 32 по Пятидесятнице **	
				'-76'=>'Мк.12:13-17','-75'=>'Мк.12:18-27','-74'=>'Мк.12:28-37','-73'=>'Мк.12:38-44','-72'=>'Мк.13:1-8','-71'=>'Лк.18:2-8','-70'=>'Лк.18:10-14',		// // О мытаре и фарисее: повтор 33 по Пятидесятнице *
				'-69'=>'Мк.13:9-13','-68'=>'Мк.13:14-23','-67'=>'Мк.13:24-31 ','-66'=>'Мк.13:31-14:2','-65'=>'Мк.14:3-9 ','-64'=>'Лк.20:45-21:4','-63'=>'Лк.15:11-32',				// О блудном сыне
				'-62'=>'Мк.11:1-11','-61'=>'Мк.14:10-42','-60'=>'Мк.14:43-15:1','-59'=>'Мк.15:1-15','-58'=>'Мк.15:22-25,33-41','-57'=>'Лк.21:8-9,25-27,33-36','-56'=>'Мф.25:31-46',	// О страшном суде 
				'-55'=>'Лк.19:29-40,22:7-39 ','-54'=>'Лк.22:39-42,45-23:1','-52'=>'Лк.23:1-34,44-56','-50'=>'Мф.6:1-13','-49'=>'Мф.6:14-21',										// Прощенное воскресение
				'-43'=>'Мк.2:23-3:5','-42'=>'Ин.1:43-51',		// 1 Великого поста
				'-36'=>'Мк.1:35-44','-35'=>'Мк.2:1-12',			// 2 Великого поста
				'-29'=>'Мк.2:14-17','-28'=>'Мк.8:34-9:1',		// 3 Великого поста
				'-22'=>'Мк.7:31-37','-21'=>'Мк.9:17-31',		// 4 Великого поста
				'-15'=>'Мк.8:27-31','-14'=>'Мк.10:32-45',		// 5 Великого поста
				'-8'=>'Ин.11:1-45','-7'=>'Ин.12:1-18',			// 6 Великого поста
				'-6'=>' Мф.24:3-35','-5'=>'Мф.24:36-26:2','-4'=>'Мф.26:6-16','-3'=>'Мф.26:1-20; Ин.13:3-17; Мф.26:21-39; Лк.22:43-45; Мф.26:40-27:2','-1'=>'Мф.28:1-20',// Страстная
				'0'=>'Ин.1:1-17', 								// Пасха
				'1'=>'Ин.1:18-28','2'=>'Лк.24:12-35','3'=>'Ин.1:35-51','4'=>'Ин.3:1-15','5'=>'Ин.2:12-22','6'=>'Ин.3:22-33','7'=>'Ин.20:19-31',							// 1 по Пасхе
				'8'=>'Ин.2:1-11 ','9'=>'Ин.3:16-21','10'=>'Ин.5:17-24','11'=>'Ин.5:24-30','12'=>'Ин.5:30-6:2','13'=>'Ин.6:14-27','14'=>'Мк.15:43-16:8',					// 2 по Пасхе
				'15'=>'Ин.4:46-54','16'=>'Ин.6:27-33','17'=>'Ин.6:35-39','18'=>'Ин.6:40-44','19'=>'Ин.6:48-54','20'=>'Ин.15:17-16:2','21'=>'Ин.5:1-15',					// 3 по Пасхе
				'22'=>'Ин.6:56-69','23'=>'Ин.7:1-13','24'=>'Ин.7:14-30','25'=>'Ин.8:12-20','26'=>'Ин.8:21-30','27'=>'Ин.8:31-42','28'=>'Ин.4:5-42',						// 4 по Пасхе
				'29'=>'Ин.8:42-51','30'=>'Ин.8:51-59','31'=>'Ин.6:5-14','32'=>'Ин.9:39-10:9','33'=>'Ин.10:17-28','34'=>'Ин.10:27-38','35'=>'Ин.9:1-38',					// 5 по Пасхе
				'36'=>'Ин.11:47-57','37'=>'Ин.12:19-36','38'=>'Ин.12:36-47','39'=>'Лк.24:36-53','40'=>'Ин.14:1-11','41'=>'Ин.14:10-21','42'=>'Ин.17:1-13',				// 6 по Пасхе
				'43'=>'Ин.14:27-15:7','44'=>'Ин.16:2-13','45'=>'Ин.16:15-23','46'=>'Ин.16:23-33','47'=>'Ин.17:18-26','48'=>'Ин.21:15-25','49'=>'Ин.7:37-52,8:12',		// 7 по Пасхе
				'50'=>'Мф.18:10-20','51'=>'Мф.4:25,5:1-13','52'=>'Мф.5:20-26','53'=>'Мф.5:27-32','54'=>'Мф.5:33-41','55'=>'Мф.5:42-48','56'=>'Мф.10:32-33,37-38,19:27-30',	// Троицкая
				'57'=>'Мф.6:31-34,7:9-11','58'=>'Мф.7:15-21','59'=>'Мф.7:21-23','60'=>'Мф.8:23-27','61'=>'Мф.9:14-17','62'=>'Мф.7:1-8','63'=>'Мф.4:18-23',				// 2 по Пятидесятнице
				'64'=>'Мф.9:36-10:8','65'=>'Мф.10:9-15','66'=>'Мф.10:16-22','67'=>'Мф.10:23-31','68'=>'Мф.10:32-36,11:1','69'=>'Мф.7:24-8:4','70'=>'Мф.6:22-33',		// 3 по Пятидесятнице
				'71'=>'Мф.11:2-15','72'=>'Мф.11:16-20','73'=>'Мф.11:20-26','74'=>'Мф.11:27-30','75'=>'Мф.12:1-8','76'=>'Мф.8:14-23','77'=>'Мф.8:5-13',					// 4 по Пятидесятнице
				'78'=>'Мф.12:9-13','79'=>'Мф.12:14-16,22-30','80'=>'Мф.12:38-45','81'=>'Мф.12:46-13:3','82'=>'Мф.13:4-9','83'=>'Мф.9:9-13','84'=>'Мф.8:28-9:1',			// 5 по Пятидесятнице	
				'85'=>'Мф.13:10-23','86'=>'Мф.13:24-30','87'=>'Мф.13:31-36','88'=>'Мф.13:36-43','89'=>'Мф.13:44-54','90'=>'Мф.9:18-26','91'=>'Мф.9:1-8',				// 6 по Пятидесятнице
				'92'=>'Мф.13:54-58','93'=>'Мф.14:1-13','94'=>'Мф.14:35-15:11','95'=>'Мф.15:12-21','96'=>'Мф.15:29-31','97'=>'Мф.10:37-11:1','98'=>'Мф.9:27-35',			// 7 по Пятидесятнице
				'99'=>'Мф.16:1-6','100'=>'Мф.16:6-12','101'=>'Мф.16:20-24','102'=>'Мф.16:24-28','103'=>'Мф.17:10-18','104'=>'Мф.12:30-37','105'=>'Мф.14:14-22',			// 8 по Пятидесятнице
				'106'=>'Мф.18:1-11','107'=>'Мф.18:18-22,19:1-2,13-15','108'=>'Мф.20:1-16','109'=>'Мф.20:17-28','110'=>'Мф.21:12-14,17-20','111'=>'Мф.15:32-39','112'=>'Мф.14:22-34',	// 9 по Пятидесятнице
				'113'=>'Мф.21:18-22','114'=>'Мф.21:23-27','115'=>'Мф.21:28-32','116'=>'Мф.21:43-46','117'=>'Мф.22:23-33','118'=>'Мф.17:24-18:4','119'=>'Мф.17:14-23',	// 10 по Пятидесятнице
				'120'=>'Мф.23:13-22','121'=>'Мф.23:23-28','122'=>'Мф.23:29-39','123'=>'Мф.24:13-28','124'=>'Мф.24:27-33,42-51','125'=>'Мф.19:3-12','126'=>'Мф.18:23-35',// 11 по Пятидесятнице
				'127'=>'Мк.1:9-15','128'=>'Мк.1:16-22','129'=>'Мк.1:23-28','130'=>'Мк.1:29-35','131'=>'Мк.2:18-22','132'=>'Мф.20:29-34','133'=>'Мф.19:16-26',			// 12 по Пятидесятнице	
				'134'=>'Мк.3:6-12','135'=>'Мк.3:13-19','136'=>'Мк.3:20-27','137'=>'Мк.3:28-35','138'=>'Мк.4:1-9','139'=>'Мф.22:15-22','140'=>'Мф.21:33-42',				// 13 по Пятидесятнице
				'141'=>'Мк.4:10-23','142'=>'Мк.4:24-34','143'=>'Мк.4:35-41','144'=>'Мк.5:1-20','145'=>'Мк.5:22-24,35-6:1','146'=>'Мф.23:1-12','147'=>'Мф.22:1-14',		// 14 по Пятидесятнице
				'148'=>'Мк.5:24-34','149'=>'Мк.6:1-7','150'=>'Мк.6:7-13','151'=>'Мк.6:30-45','152'=>'Мк.6:45-53','153'=>'Мф.24:1-13','154'=>'Мф.22:35-46',				// 15 по Пятидесятнице
				'155'=>'Мк.6:54-7:8','156'=>'Мк.7:5-16','157'=>'Мк.7:14-24','158'=>'Мк.7:24-30','159'=>'Мк.8:1-10','160'=>'Мф.24:34-44','161'=>'Мф.25:14-30',			// 16 по Пятидесятнице
				'162'=>'Мк.10:46-52','163'=>'Мк.11:11-23','164'=>'Мк.11:23-26','165'=>'Мк.11:27-33','166'=>'Мк.12:1-12','167'=>'Мф.25:1-13','168'=>'Мф.15:21-28',		// 17 по Пятидесятнице ***
	/*Лк*/		'169'=>'Лк.3:19-22','170'=>'Лк.3:23-4:1','171'=>'Лк.4:1-15','172'=>'Лк.4:16-22','173'=>'Лк.4:22-30','174'=>'Лк.4:31-36','175'=>'Лк.5:1-11',				// 18 по Пятидесятнице
				'176'=>'Лк.4:37-44','177'=>'Лк.5:12-16 ','178'=>'Лк.5:33-39','179'=>'Лк.6:12-19','180'=>'Лк.6:17-23','181'=>'Лк.5:17-26','182'=>'Лк.6:31-36',			// 19 по Пятидесятнице
				'183'=>'Лк.6:24-30','184'=>'Лк.6:37-45','185'=>'Лк.6:46-7:1','186'=>'Лк.7:17-30','187'=>'Лк.7:31-35','188'=>'Лк.5:27-32','189'=>'Лк.7:11-16',			// 20 по Пятидесятнице
				'190'=>'Лк.7:36-50','191'=>'Лк.8:1-3','192'=>'Лк.8:22-25','193'=>'Лк.9:7-11','194'=>'Лк.9:12-18','195'=>'Лк.6:1-10','196'=>'Лк.8:5-15',					// 21 по Пятидесятнице
				'197'=>'Лк.9:18-22','198'=>'Лк.9:23-27','199'=>'Лк.9:44-50','200'=>'Лк.9:49-56','201'=>'Лк.10:1-15','202'=>'Лк.7:2-10','203'=>'Лк.16:19-31',			// 22 по Пятидесятнице
				'204'=>'Лк.10:22-24','205'=>'Лк.11:1-10','206'=>'Лк.11:9-13','207'=>'Лк.11:14-23','208'=>'Лк.11:23-26','209'=>'Лк.8:16-21','210'=>'Лк.8:26-39',			// 23 по Пятидесятнице
				'211'=>'Лк.11:29-33','212'=>'Лк.11:34-41','213'=>'Лк.11:42-46','214'=>'Лк.11:47-12:1','215'=>'Лк.12:2-12','216'=>'Лк.9:1-6','217'=>'Лк.8:41-56',		// 24 по Пятидесятнице
				'218'=>'Лк.12:13-15,22-31','219'=>'Лк.12:42-48','220'=>'Лк.12:48-59','221'=>'Лк.13:1-9','222'=>'Лк.13:31-35','223'=>'Лк.9:37-43','224'=>'Лк.10:25-37',	// 25 по Пятидесятнице
				'225'=>'Лк.14:12-15','226'=>'Лк.14:25-35','227'=>'Лк.15:1-10','228'=>'Лк.16:1-9','229'=>'Лк.16:15-18,17:1-4','230'=>'Лк.9:57-62','231'=>'Лк.12:16-21',	// 26 по Пятидесятнице
				'232'=>'Лк.17:20-25','233'=>'Лк.17:26-37','234'=>'Лк.18:15-17,26-30','235'=>'Лк.18:31-34','236'=>'Лк.19:12-28','237'=>'Лк.10:19-21','238'=>'Лк.13:10-17',// 27 по Пятидесятнице
				'239'=>'Лк.19:37-44','240'=>'Лк.19:45-48','241'=>'Лк.20:1-8','242'=>'Лк.20:9-18','243'=>'Лк.20:19-26','244'=>'Лк.12:32-40','245'=>'Лк.14:16-24',		// 28 по Пятидесятнице
				'246'=>'Лк.20:27-44','247'=>'Лк.21:12-19','248'=>'Лк.21:5-7,10-11,20-24','249'=>'Лк.21:28-33','250'=>'Лк.21:37-22:8','251'=>'Лк.13:18-29','252'=>'Лк.17:12-19',// 29 по Пятидесятнице
				'253'=>'Мк.8:11-21','254'=>'Мк.8:22-26','255'=>'Мк.8:30-34','256'=>'Мк.9:10-16','257'=>'Мк.9:33-41','258'=>'Лк.14:1-11','259'=>'Лк.18:18-27',			// 30 по Пятидесятнице *****
				'260'=>'Мк.9:42-10:1','261'=>'Мк.10:2-12','262'=>'Мк.10:11-16','263'=>'Мк.10:17-27','264'=>'Мк.10:23-32','265'=>'Лк.16:10-15','266'=>'Лк.18:35-43',		// 31 по Пятидесятнице ****
				'267'=>'Мк.10:46-52','268'=>'Мк.11:11-23','269'=>'Мк.11:23-26','270'=>'Мк.11:27-33','271'=>'Мк.12:1-12','272'=>'Лк.17:3-10','273'=>'Лк.19:1-10',		// 32 по Пятидесятнице **	
				'274'=>'Мк.12:13-17','275'=>'Мк.12:18-27','276'=>'Мк.12:28-37','277'=>'Мк.12:38-44','278'=>'Мк.13:1-8','279'=>'Лк.18:2-8','280'=>'Лк.18:10-14'];		// 33 по Пятидесятнице *

		list($year, $month, $day) = explode ('-', $date); 
		$interval = $this->bg_date_easter_dif($date, $year);
		$key = $interval;
		
		// Вычисляем Крещенскую отступку
		$end_of_cycle = $this->bg_get_end_of_cycle ($year);						// Конец годового круга
		$interval_end_of_cycle = $this->bg_date_easter_dif($end_of_cycle, $year);	// Количество дней до Конца годового круга от Пасхи (отрицательное число)
		$shift_еpiphany = 70 + $interval_end_of_cycle;						// Крещенская отступка(-)

		// Вычисляем Воздвиженскую отступку/преступку
		$sunday_after_exaltation = $this->bg_get_sunday_after_exaltation($year);	// Неделя по Воздвижению
		$interval_exaltation = $this->bg_date_easter_dif($sunday_after_exaltation, $year);// Количество дней до Недели по Воздвижению от Пасхи
		$shift_exaltation = 168 - $interval_exaltation;						// Воздвиженская отступка(-)/преступка(+)

		// Разбиваем год на 3 части
		if ($date <= $end_of_cycle) {										// Завершаем круг чтения прошлого года
			$before_end_of_cycle = $interval - $interval_end_of_cycle;		// Дней до конца годового круга (-)
			$key = 280 + $before_end_of_cycle;
		} elseif ($date <= $sunday_after_exaltation) {						// После Конца годового круга до Недели по Воздвижению
			// Это смещение требуется, если использовать вариант 30, 31, 17, 32, 33 седмицы при 5 недельной Крещенской отступке,
			// для 3 или 4 недельной Крещенской отступки просто игнорируем 17 седмицу
			if ($interval <= -84 && ($shift_еpiphany < -14 && $shift_еpiphany > -35)) { 
				$key -= 7;
			}
			if ($shift_exaltation < 0 && $interval > 168)	{				// Если случилась Воздвиженская отступка, 
				$key += $shift_exaltation;									// то повторяем одну или две последние седмицы до Недели по Воздвижению (17 или 16 и 17 по Пятидесятнице)
				$key += -10;												// Вариант, используемый Патриархией в Богослужебных Указаниях (11 или 10 и 11 по Пятидесятнице)
			}
		} else {															// В любом случае после Недели по Воздвижению читаем Евангелие от Луки
			$key += $shift_exaltation;									
		}
		
		// Евангельское зачало 28 Недели читается в Неделю святых праотец,
		// поэтому они меняются местами с соответствующими рядовым чтениями той Недели,
		// на которую придется в данном году Неделя свв. праотец.
		$sunday_holy_forefathers = $this->bg_get_sunday_holy_forefathers($year);	// Неделя по Святых Праотец
		$interval_holy_forefathers = $this->bg_date_easter_dif($sunday_holy_forefathers, $year);	// Количество дней до Недели Святых Праотец
		if ($date == $sunday_holy_forefathers) {
			$key = 245;
		} elseif ($key == 245) {
			$key = $interval_holy_forefathers + $shift_exaltation;			
		}
		
		if (array_key_exists($key, $gl)) return $gl[$key];
		else return "";
	}

/*========================================================================================
	Чтения Апостола на Литургии
	Параметры:
		$date - дата в формате Y-m-d
	Возвращает:
		Текст, содержащий список ссылок на Апостола
 ========================================================================================*/
	public function bg_Apostol_at_Liturgy ($date) {
		$al = [	'-104'=>'Евр.8:7-13','-103'=>'Евр.9:8-10,15-23','-102'=>'Евр.10:1-18','-101'=>'Евр.10:35-11:7 ','-100'=>'Евр.11:8,11-16','-99'=>'Еф.5:1-8','-98'=>'Кол.3:12-16',	// Повтор 30 по Пятидесятнице ****	
				'-97'=>'Евр.11:17-23,27-31','-96'=>'Евр.12:25-26,13:22-25','-95'=>'Иак.1:1-18','-94'=>'Иак.1:19-27','-93'=>'Иак.2:1-13','-92'=>'Кол.1:3-6','-91'=>'1 Тим.1:15-17',	// Повтор 31 по Пятидесятнице ***
				'-90'=>'Еф.1:22-2:3','-89'=>'Еф.2:19-3:7','-88'=>'Еф.3:8-21','-87'=>'Еф.4:14-19','-86'=>'Еф.4:17-25','-85'=>'1 Кор.14:20-25','-84'=>'2 Кор.6:16-7:1',				// Повтор 17 по Пятидесятнице *****
				'-83'=>'Иак.2:14-26','-82'=>'Иак.3:1-10','-81'=>'Иак.3:11-4:6','-80'=>'Иак.4:7-5:9','-79'=>'1 Пет.1:1-2,10-12,2:6-10','-78'=>'1 Сол.5:14-23','-77'=>'1 Тим.4:9-15',	// О Закхее: повтор 32 по Пятидесятнице **	
				'-76'=>'1 Пет.2:21-3:9','-75'=>'1 Пет.3:10-22','-74'=>'1 Пет.4:1-11','-73'=>'1 Пет.4:12-5:5','-72'=>'2 Пет.1:1-10','-71'=>'2 Тим.2:11-19','-70'=>'2 Тим.3:10-15',	// О мытаре и фарисее: повтор 33 по Пятидесятнице *
				'-69'=>'2 Пет.1:20-2:9','-68'=>'2 Пет.2:9-22','-67'=>'2 Пет.3:1-18','-66'=>'1 Ин.1:8-2:6','-65'=>'1 Ин.2:7-17','-64'=>'2 Тим.3:1-9','-63'=>'1 Кор.6:12-20',			// О блудном сыне
				'-62'=>'1 Ин.2:18-3:10','-61'=>'1 Ин.3:11-20','-60'=>'1 Ин.3:21-4:6','-59'=>'1 Ин.4:20-5:21','-58'=>'2 Ин.1:1-13','-57'=>'1 Кор.10:23-28','-56'=>'1 Кор.8:8-9:2',	// О страшном суде
				'-55'=>'3 Ин.1:1-15','-54'=>'Иуд.1:1-10','-52'=>'Иуд.1:11-25','-50'=>'Рим.14:19-26','-49'=>'Рим.13:11-14:4',														// Прощенное воскресение
				'-43'=>'Евр.1:1-12','-42'=>'Евр.11:24-26,32-12:2',	// 1 Великого поста
				'-36'=>'Евр.3:12-16','-35'=>'Евр.1:10-2:3',			// 2 Великого поста
				'-29'=>'Евр.10:32-38','-28'=>'Евр.4:14-5:6',		// 3 Великого поста
				'-22'=>'Евр.6:9-12','-21'=>'Евр.6:13-20',			// 4 Великого поста
				'-15'=>'Евр.9:24-28','-14'=>'Евр.9:11-14',			// 5 Великого поста
				'-8'=>'Евр.12:28-13:8','-7'=>'Флп.4:4-9',			// 6 Великого поста
				'-3'=>'1 Кор.11:23-32','-1'=>'Рим.6:3-11',			// Страстная
				'0'=>'Деян.1:1-8',									// Пасха
				'1'=>'Деян.1:12-17,21-26','2'=>'Деян.2:14-21','3'=>'Деян.2:22-36','4'=>'Деян.2:38-43','5'=>'Деян.3:1-8','6'=>'Деян.3:11-16','7'=>'Деян.5:12-20',					// 1 по Пасхе
				'8'=>'Деян.3:19-26','9'=>'Деян.4:1-10','10'=>'Деян.4:13-22','11'=>'Деян.4:23-31','12'=>'Деян.5:1-11','13'=>'Деян.5:21-33','14'=>'Деян.6:1-7',						// 2 по Пасхе
				'15'=>'Деян.6:8-7:5,47-60','16'=>'Деян.8:5-17','17'=>'Деян.8:18-25','18'=>'Деян.8:26-39','19'=>'Деян.8:40-9:19','20'=>'Деян.9:20-31','21'=>'Деян.9:32-42',			// 3 по Пасхе
				'22'=>'Деян.10:1-16','23'=>'Деян.10:21-33','24'=>'Деян.14:6-18','25'=>'Деян.10:34-43','26'=>'Деян.10:44-11:10','27'=>'Деян.12:1-11','28'=>'Деян.11:19-26,29-30',	// 4 по Пасхе
				'29'=>'Деян.12:12-17','30'=>'Деян.12:25-13:12','31'=>'Деян.13:13-24','32'=>'Деян.14:20-27','33'=>'Деян.15:5-34','34'=>'Деян.15:35-41','35'=>'Деян.16:16-34',		// 5 по Пасхе
				'36'=>'Деян.17:1-15','37'=>'Деян.17:19-28','38'=>'Деян.18:22-28','39'=>'Деян.1:1-12','40'=>'Деян.19:1-8','41'=>'Деян.20:7-12','42'=>'Деян.20:16-18,28-36',			// 6 по Пасхе
				'43'=>'Деян.21:8-14','44'=>'Деян.21:26-32','45'=>'Деян.23:1-11','46'=>'Деян.25:13-19','47'=>'Деян.27:1-44','48'=>'Деян.28:1-31','49'=>'Деян.2:1-11',				// 7 по Пасхе
				'50'=>'Еф.5:9-19','51'=>'Рим.1:1-7,13-17','52'=>'Рим.1:18-27','53'=>'Рим.1:28-2:9','54'=>'Рим.2:14-29','55'=>'Рим.1:7-12','56'=>'Евр.11:33-12:2',					// Троицкая
				'57'=>'Рим.2:28-3,18','58'=>'Рим.4:4-12','59'=>'Рим.4:13-25','60'=>'Рим.5:10-16','61'=>'Рим.5:17-6:2','62'=>'Рим.3:19-26','63'=>'Рим.2:10-16',						// 2 по Пятидесятнице
				'64'=>'Рим.7:1-13','65'=>'Рим.7:14-8:2','66'=>'Рим.8:2-13','67'=>'Рим.8:22-27','68'=>'Рим.9:6-19','69'=>'Рим.3:28-4:3','70'=>'Рим.5:1-10',							// 3 по Пятидесятнице
				'71'=>'Рим.9:18-33','72'=>'Рим.10:11-11:2','73'=>'Рим.11:2-12','74'=>'Рим.11:13-24','75'=>'Рим.11:25-36','76'=>'Рим.6:11-17','77'=>'Рим.6:18-23',					// 4 по Пятидесятнице
				'78'=>'Рим.12:4-5,15-21','79'=>'Рим.14:9-18','80'=>'Рим.15:7-16','81'=>'Рим.15:17-29','82'=>'Рим.16:1-16','83'=>'Рим.8:14-21','84'=>'Рим.10:1-10',					// 5 по Пятидесятнице	
				'85'=>'Рим.16:17-24','86'=>'1 Кор.1:1-9','87'=>'1 Кор.2:9-3:8','88'=>'1 Кор.3:18-23','89'=>'1 Кор.4:5-8','90'=>'Рим.9:1-5','91'=>'Рим.12:6-14',						// 6 по Пятидесятнице
				'92'=>'1 Кор.5:9-6:11','93'=>'1 Кор.6:20-7:12','94'=>'1 Кор.7:12-24','95'=>'1 Кор.7:24-35','96'=>'1 Кор.7:35-8:7','97'=>'Рим.12:1-3','98'=>'Рим.15:1-7',			// 7 по Пятидесятнице
				'99'=>'1 Кор.9:13-18','100'=>'1 Кор.10:5-12','101'=>'1 Кор.10:12-22','102'=>'1 Кор.10:28-11:7','103'=>'1 Кор.11:8-22','104'=>'Рим.13:1-10','105'=>'1 Кор.1:10-18',	// 8 по Пятидесятнице
				'106'=>'1 Кор.11:31-12:6','107'=>'1 Кор.12:12-26','108'=>'1 Кор.13:4-14:5','109'=>'1 Кор.14:6-19','110'=>'1 Кор.14:26-40','111'=>'Рим.14:6-9','112'=>'1 Кор.3:9-17',// 9 по Пятидесятнице
				'113'=>'1 Кор.15:12-19','114'=>'1 Кор.15:29-38','115'=>'1 Кор.16:4-12','116'=>'2 Кор.1:1-7','117'=>'2 Кор.1:12-20','118'=>'Рим.15:30-33','119'=>'1 Кор.4:9-16',		// 10 по Пятидесятнице
				'120'=>'2 Кор.2:4-15','121'=>'2 Кор.2:14-3:3','122'=>'2 Кор.3:4-11','123'=>'2 Кор.4:1-6','124'=>'2 Кор.4:13-18','125'=>'1 Кор.1:3-9','126'=>'1 Кор.9:2-12',			// 11 по Пятидесятнице
				'127'=>'2 Кор.5:10-15','128'=>'2 Кор.5:15-21','129'=>'2 Кор.6:11-16','130'=>'2 Кор.7:1-10','131'=>'2 Кор.7:10-16','132'=>'1 Кор.1:26-29','133'=>'1 Кор.15:1-11',	// 12 по Пятидесятнице	
				'134'=>'2 Кор.8:7-15','135'=>'2 Кор.8:16-9:5','136'=>'2 Кор.9:12-10:7','137'=>'2 Кор.10:7-18','138'=>'2 Кор.11:5-21','139'=>'1 Кор.2:6-9','140'=>'1 Кор.16:13-24',	// 13 по Пятидесятнице
				'141'=>'2 Кор.12:10-19','142'=>'2 Кор.12:20-13:2','143'=>'2 Кор.13:3-13','144'=>'Гал.1:1-10,20-2:5','145'=>'Гал.2:6-10','146'=>'1 Кор.4:1-5','147'=>'2 Кор.1:21-2:4',// 14 по Пятидесятнице
				'148'=>'Гал.2:11-16','149'=>'Гал.2:21-3:7','150'=>'Гал.3:15-22','151'=>'Гал.3:23-4:5','152'=>'Гал.4:8-21','153'=>'1 Кор.4:17-5:5','154'=>'2 Кор.4:6-15',			// 15 по Пятидесятнице
				'155'=>'Гал.4:28-5:10','156'=>'Гал.5:11-21','157'=>'Гал.6:2-10','158'=>'Еф.1:1-9','159'=>'Еф.1:7-17','160'=>'1 Кор.10:23-28','161'=>'2 Кор.6:1-10',					// 16 по Пятидесятнице
				'162'=>'Еф.1:22-2:3','163'=>'Еф.2:19-3:7','164'=>'Еф.3:8-21','165'=>'Еф.4:14-19','166'=>'Еф.4:17-25','167'=>'1 Кор.14:20-25','168'=>'2 Кор.6:16-7:1',				// 17 по Пятидесятнице ***
				'169'=>'Еф.4:25-32','170'=>'Еф.5:20-26','171'=>'Еф.5:25-33','172'=>'Еф.5:33-6:9','173'=>'Еф.6:18-24','174'=>'1 Кор.15:39-45','175'=>'2 Кор.9:6-11',					// 18 по Пятидесятнице
				'176'=>'Флп.1:1-7','177'=>'Флп.1:8-14','178'=>'Флп.1:12-20','179'=>'Флп.1:20-27','180'=>'Флп.1:27-2:4','181'=>'1 Кор.15:58-16:3','182'=>'2 Кор.11:31-12:9',			// 19 по Пятидесятнице
				'183'=>'Флп.2:12-16','184'=>'Флп.2:17-23','185'=>'Флп.2:24-30','186'=>'Флп.3:1-8','187'=>'Флп.3:8-19','188'=>'2 Кор.1:8-11','189'=>'Гал.1:11-19',					// 20 по Пятидесятнице
				'190'=>'Флп.4:10-23','191'=>'Кол.1:1-2,7-11','192'=>'Кол.1:18-23','193'=>'Кол.1:24-29','194'=>'Кол.2:1-7','195'=>'2 Кор.3:12-18','196'=>'Гал.2:16-20',				// 21 по Пятидесятнице
				'197'=>'Кол.2:13-20','198'=>'Кол.2:20-3:3','199'=>'Кол.3:17-4:1','200'=>'Кол.4:2-9','201'=>'Кол.4:10-18','202'=>'2 Кор.5:1-10','203'=>'Гал.6:11-18',				// 22 по Пятидесятнице
				'204'=>'1 Сол.1:1-5','205'=>'1 Сол.1:6-10','206'=>'1 Сол.2:1-8','207'=>'1 Сол.2:9-14','208'=>'1 Сол.2:14-19','209'=>'2 Кор.8:1-5','210'=>'Еф.2:4-10',				// 23 по Пятидесятнице
				'211'=>'1 Сол.2:20-3:8','212'=>'1 Сол.3:9-13','213'=>'1 Сол.4:1-12','214'=>'1 Сол.5:1-8 ','215'=>'1 Сол.5:9-13,24-28','216'=>'2 Кор.11:1-6','217'=>'Еф.2:14-22',	// 24 по Пятидесятнице
				'218'=>'2 Сол.1:1-10','219'=>'2 Сол.1:10-2:2','220'=>'2 Сол.2:1-12','221'=>'2 Сол.2:13-3:5','222'=>'2 Сол.3:6-18','223'=>'Гал.1:3-10','224'=>'Еф.4:1-6',			// 25 по Пятидесятнице
				'225'=>'1 Тим.1:1-7','226'=>'1 Тим.1:8-14','227'=>'1 Тим.1:18-20,2:8-15','228'=>'1 Тим.3:1-13','229'=>'1 Тим.4:4-8,16','230'=>'Гал.3:8-12','231'=>'Еф.5:8-19',		// 26 по Пятидесятнице
				'232'=>'1 Тим.5:1-10','233'=>'1 Тим.5:11-21','234'=>'1 Тим.5:22-6:11','235'=>'1 Тим.6:17-21','236'=>'2 Тим.1:1-2,8-18','237'=>'Гал.5:22-6:2','238'=>'Еф.6:10-17',	// 27 по Пятидесятнице
				'239'=>'2 Тим.2:20-26','240'=>'2 Тим.3:16-4:4','241'=>'2 Тим.4:9-22','242'=>'Тит.1:5-2:1','243'=>'Тит.1:15-2:10','244'=>'Еф.1:16-23','245'=>'Кол.1:12-18',			// 28 по Пятидесятнице
				'246'=>'Евр.3:5-11,17-19','247'=>'Евр.4:1-13','248'=>'Евр.5:11-6:8 ','249'=>'Евр.7:1-6 ','250'=>'Евр.7:18-25','251'=>'Еф.2:11-13','252'=>'Кол.3:4-11',				// 29 по Пятидесятнице 
				'253'=>'Евр.8:7-13','254'=>'Евр.9:8-10,15-23','255'=>'Евр.10:1-18','256'=>'Евр.10:35-11:7 ','257'=>'Евр.11:8,11-16','258'=>'Еф.5:1-8','259'=>'Кол.3:12-16',			// 30 по Пятидесятнице *****
				'260'=>'Евр.11:17-23,27-31','261'=>'Евр.12:25-26,13:22-25','262'=>'Иак.1:1-18','263'=>'Иак.1:19-27','264'=>'Иак.2:1-13','265'=>'Кол.1:3-6','266'=>'1 Тим.1:15-17',	// 31 по Пятидесятнице ****
				'267'=>'Иак.2:14-26','268'=>'Иак.3:1-10','269'=>'Иак.3:11-4:6','270'=>'Иак.4:7-5:9','271'=>'1 Пет.1:1-2 ,10-12,2:6-10','272'=>'1 Сол.5:14-23','273'=>'1 Тим.4:9-15',// 32 по Пятидесятнице **
				'274'=>'1 Пет.2:21-3:9','275'=>'1 Пет.3:10-22','276'=>'1 Пет.4:1-11','277'=>'1 Пет.4:12-5:5','278'=>'2 Пет.1:1-10','279'=>'2 Тим.2:11-19','280'=>'2 Тим.3:10-15'];	// 33 по Пятидесятнице *


		list($year, $month, $day) = explode ('-', $date); 
		$interval = $this->bg_date_easter_dif($date, $year);
		$key = $interval;
		
		// Вычисляем Крещенскую отступку
		$end_of_cycle = $this->bg_get_end_of_cycle ($year);						// Конец годового круга
		$interval_end_of_cycle = $this->bg_date_easter_dif($end_of_cycle, $year);	// Количество дней до Конца годового круга от Пасхи (отрицательное число)
		$shift_еpiphany = 70 + $interval_end_of_cycle;						// Крещенская отступка(-)

		// Вычисляем Воздвиженскую отступку/преступку предыдущего года
		$sunday_after_exaltation = $this->bg_get_sunday_after_exaltation($year-1);	// Неделя по Воздвижению
		$interval_exaltation = $this->bg_date_easter_dif($sunday_after_exaltation, $year-1);// Количество дней до Недели по Воздвижению от Пасхи
		$shift_exaltation = 168 - $interval_exaltation;						// Воздвиженская отступка(-)/преступка(+)

		
		// Разбиваем год на 2 части
		if ($date <= $end_of_cycle) {										// Завершаем круг чтения прошлого года
			$key = $this->bg_date_easter_dif($date, $year-1);
			if ($shift_exaltation < 0) {									// Если в прошлом году случилась Воздвиженская отступка, 
				$key += $shift_exaltation;									// то повторяем одну или две последние седмицы до Недели по Богоявлению (33 или 32 и 33 по Пятидесятнице)
			}
		} else {
			// Это смещение требуется, если использовать вариант 30, 31, 17, 32, 33 седмицы при 5 недельной Крещенской отступке,
			// для 3 или 4 недельной Крещенской отступки просто игнорируем 17 седмицу
			if ($interval <= -84 && ($shift_еpiphany < -14 && $shift_еpiphany > -35)) { 
				$key -= 7;
			}
		}
		
		// Апостольское зачало 29 Недели читается в Неделю святых праотец,
		// поэтому они меняются местами с соответствующими рядовым чтениями той Недели,
		// на которую придется в данном году Неделя свв. праотец.
		$sunday_holy_forefathers = $this->bg_get_sunday_holy_forefathers($year);	// Неделя по Святых Праотец
		$interval_holy_forefathers = $this->bg_date_easter_dif($sunday_holy_forefathers, $year);	// Количество дней до Недели Святых Праотец
		if ($date == $sunday_holy_forefathers) {
			$key = 252;
		} elseif ($key == 252) {
			$key = $interval_holy_forefathers;
		}
		
		if (array_key_exists($key, $al)) return $al[$key];
		else return "";
	}

/*========================================================================================
	Чтения Евангелие на Утрене
	Параметры:
		$date - дата в формате Y-m-d
	Возвращает:
		Текст, содержащий список ссылок на Евангелие
 ========================================================================================*/
	public function bg_Gospel_at_Matins ($date) {
		// В течение года читаются 11 воскресных зачал покругу, начиная со Дня Всех Святых до Недели предшествующей Вербному воскресенью
		$gm = [ 'Мф.28:16-20','Мк.16:1–8','Мк.16:9-20','Лк.24:1-12','Лк.24:12–35','Лк.24:36–53','Ин.20:1-10','Ин.20:11-18','Ин.20:19–31','Ин.21:1-14','Ин.21:15-25'];

		
		list($year, $month, $day) = explode ('-', $date); 
		$easter = $this->bg_get_easter($year);
		$interval = $this->bg_date_easter_dif($date, $year);
		$is_sunday = (date_format(date_create($date), 'N') == '7')?true:false;
		
		$interval_last_year = $this->bg_date_easter_dif($date, $year-1);	// Дней от предыдущей Пасхи
		$christmas = date( 'Y-m-d', mktime ( 0, 0, 0, 12, 25+$this->bg_ddif($year), $year-1) );											// Рождество
		$еpiphany = date( 'Y-m-d', mktime ( 0, 0, 0, 01, 06+$this->bg_ddif($year), $year-1) );												// Богоявление
		$palm_sunday = date_format(date_sub(date_create($easter), date_interval_create_from_date_string("7 days")), 'Y-m-d');		// Вербное воскресенье
		$low_sunday = date_format(date_add(date_create($easter), date_interval_create_from_date_string("7 days")), 'Y-m-d');		// Фомина Неделя
		$allhallows_sunday = date_format(date_add(date_create($easter), date_interval_create_from_date_string("56 days")), 'Y-m-d');// День Всех Святых

		// Весь год только воскресные дни за исключением Страстной седмицы
		// Разбиваем год на 5 частей
		if ($is_sunday && $date < $palm_sunday) {								// До Вербного воскресенья завершаем круг чтения прошлого года
			if ($date == $christmas || $date == $еpiphany) return "";			// В Рождество и Богоявление, рядовое воскресное зачало не читается
			$week = ($interval_last_year/7-8)%11;	
		} elseif ($date >= $palm_sunday && $date < $easter) {					// На Страстной седмице и в Вербное воскресенье
			$gm_m = ['-7'=>'Мф.21:1-11,15-17','-6'=>'Мф.21:18-43','-5'=>'Мф.22:15 –23,39','-4'=>'Ин.12:17-50','-3'=>'Лк.22:1-39','-1'=>'1Кор.5:6-8; Гал.3:13-14; Мф.27:62-66'];
			return $gm_m[$interval];
		} elseif ($is_sunday && $date > $easter && $date < $allhallows_sunday) {// От Фоминой Недели до Троицы (7 седмиц) 
			$order = [0,2,3,6,7,9,8];											// измененный порядок чтения зачал
			$week = $order[($interval/7-1)%11];						
		} elseif ($is_sunday && $date >= $allhallows_sunday) {					// От Дня Всех Святых
			$week = ($interval/7-8)%11;		
		} else return "";
		
		return $gm[$week];
	}
}
