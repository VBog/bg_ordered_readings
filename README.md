### Bg Ordered Readings  ###

Contributors: VBog

Tags: чтения, литургия, утреня, Евангелие, Апостол

License: GPLv2

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Круг рядовых чтений на богослужениях


## Description ##

**ФУНКЦИИ ДЛЯ РАСЧЕТА РЯДОВЫХ ЧТЕНИЙ**

`bg_Gospel_at_Liturgy ($date)` 	- чтение Евангелие на Литургии

`bg_Apostol_at_Liturgy ($date)` - чтение Апостола на Литургии

`bg_Gospel_at_Matins ($date)` 	- чтение Евангелие на Утрене

*Параметры:*

`$date` - дата по новому стилю в формате Y-m-d

Чтобы вывести в календаре полный список чтений, необходимо создать БД чтений на Праздники и святым 
(см.https://azbyka.ru/days/p-ukazatel-evangelskih-i-apostolskih-chtenij-na-kazhdyj-den-goda#prp ), 
а затем, используя эти функции, сформировать окончательную выдачу по чтениям, 
учитыая нижеприведенные правила:

1.	В великие праздники Господские, Богородичные и святых, которым положено бдение, 
рядовые Апостол и Евангелие не читаются, а только данному празднику или святому. 
2.	Но если великий Богородичен праздник или святого с бдением случится в воскресный день, 
тогда читается сначала воскресный рядовой Апостол и Евангелие, а потом уже праздника или святого.
3.	Чтения всем остальным  иконам Пресятой Богородице, ангелам, апостолам и святым 
выводим отдельной строкой после основных чтений с указанием лика святости.
		
https://stackoverflow.com/questions/23793062/can-forks-be-synced-automatically-in-github/61574295#61574295

## Changelog ##

= 1.0 =
* Initial release