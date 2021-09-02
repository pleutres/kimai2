<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserVacation;

/**
 * Monthly statistics
 */
class AbstractMonth
{
    /**
     * @var integer
     */
    protected $month;
    /**
     * @var integer
     */
    protected $year;

    /**
     * @param string $month
     */
    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

    }


    /**
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    public function getWorkingDays(): int {

        $month_date = strtotime($this->year . '-' . $this->month . '-01');
        $date_start = date('Y-m-1', $month_date);
        $date_stop = date('Y-m-t', $month_date);

        error_log( date('Y', strtotime($date_stop)));
        error_log($date_stop);

        return $this->getNOpenDays(strtotime($date_start), strtotime($date_stop));

    }

    // Fonction permettant de compter le nombre de jours ouvrés entre deux dates
    private function getNOpenDays($date_start, $date_stop) {
        $arr_bank_holidays = array(); // Tableau des jours feriés



        // On boucle dans le cas où l'année de départ serait différente de l'année d'arrivée
        $diff_year = date('Y', $date_stop) - date('Y', $date_start);
        for ($i = 0; $i <= $diff_year; $i++) {
            $year = (int)date('Y', $date_start) + $i;
            // Liste des jours feriés
            $arr_bank_holidays[] = '1_1_'.$year; // Jour de l'an
            $arr_bank_holidays[] = '1_5_'.$year; // Fete du travail
            $arr_bank_holidays[] = '8_5_'.$year; // Victoire 1945
            $arr_bank_holidays[] = '14_7_'.$year; // Fete nationale
            $arr_bank_holidays[] = '15_8_'.$year; // Assomption
            $arr_bank_holidays[] = '1_11_'.$year; // Toussaint
            $arr_bank_holidays[] = '11_11_'.$year; // Armistice 1918
            $arr_bank_holidays[] = '25_12_'.$year; // Noel

            // Récupération de paques. Permet ensuite d'obtenir le jour de l'ascension et celui de la pentecote
            $easter = easter_date($year);
            $arr_bank_holidays[] = date('j_n_'.$year, $easter + 86400); // Paques
            $arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*39)); // Ascension
            $arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*50)); // Pentecote
        }
        //print_r($arr_bank_holidays);
        $nb_days_open = 0;
        // Mettre <= si on souhaite prendre en compte le dernier jour dans le décompte
        while ($date_start <= $date_stop) {
            // Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour férié, on incrémente les jours ouvrés
            if (!in_array(date('w', $date_start), array(0, 6))
            && !in_array(date('j_n_'.date('Y', $date_start), $date_start), $arr_bank_holidays)) {
                $nb_days_open++;
            }
            $date_start = mktime(date('H', $date_start), date('i', $date_start), date('s', $date_start), date('m', $date_start), date('d', $date_start) + 1, date('Y', $date_start));
        }
        return $nb_days_open;
    }

}
