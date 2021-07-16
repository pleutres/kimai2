<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Timesheet;
use App\Entity\User;
use App\Export\ServiceExport;
use App\Model\UserVacation\VacationMonth;
use App\Model\UserVacation\VacationYear;
use App\Repository\TimesheetRepository;
use App\Repository\UserInvoiceRepository;
use App\Export\UserInvoice\XlsxRenderer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin/uservacation")
 * @Security("is_granted('role_permissions')")
 */
class VacationController extends AbstractController
{

    private $renderer;
    private $logger;

    /**
     * @param TimesheetRepository $timesheet
     * @param ServiceExport $export
     */
    public function __construct(XlsxRenderer $renderer, LoggerInterface $logger = null)
    {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }

    /**
     * @Route(path="/", defaults={"page": 1}, name="vacation_user_admin", methods={"GET"})
     * @Security("is_granted('view_other_timesheet')")
     *
     * @param Request $request
     * @return Response
     */
    public function index($page, Request $request)
    {
        $years = [];
        $this->getMonthlyActivity($years, 'vacation', $activity = 'Vacation');
        $this->getMonthlyActivity($years, 'nonpaid', $activity = 'Non paid vacation');
        $this->getMonthlyActivity($years, 'total');

        $viewVars = [
            'years' => $years,
        ];

        return $this->render('uservacations/index.html.twig', $viewVars);
    }

    /**
     * Returns an array of Year statistics.
     *
     * @param User|null $user
     * @param DateTime|null $begin
     * @param DateTime|null $end
     * @return Year[]
     */
    public function getMonthlyActivity(&$years, $setter, $activity = null, User $user = null, ?DateTime $begin = null, ?DateTime $end = null)
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        $qb->select('SUM(t.duration) as duration, MONTH(t.begin) as month, YEAR(t.begin) as year, user.alias as ualias')
            ->from(Timesheet::class, 't')
            ->leftJoin('t.user', 'user')
            ->leftJoin('t.activity', 'activity')
            ->leftJoin('t.project', 'project')
        ;

//         $qb->andWhere()->eq('', 'Vacation');
//         $qb->andWhere()->eq('project.name', ('Vacation');
        if ($activity != null) {
            $qb->andWhere('activity.name = :activity')->setParameter('activity', $activity);
            $qb->andWhere('project.name = :project')->setParameter('project', $activity);
        }
        $qb->andWhere('activity.name <> :publichd')->setParameter('publichd', 'Public holiday');

        if (!empty($begin)) {
            $qb->andWhere($qb->expr()->gte($this->getDatetimeFieldSql('t.begin'), ':from'))
                ->setParameter('from', $begin);
        } else {
            $qb->andWhere($qb->expr()->isNotNull('t.begin'));
        }

        if (!empty($end)) {
            $qb->andWhere($qb->expr()->lte($this->getDatetimeFieldSql('t.end'), ':to'))
                ->setParameter('to', $end);
        } else {
            $qb->andWhere($qb->expr()->isNotNull('t.end'));
        }

        if (null !== $user) {
            $qb->andWhere('t.user = :user')
                ->setParameter('user', $user);
        }

        $qb
            ->orderBy('year', 'DESC')
            ->addOrderBy('month', 'DESC')
            ->addOrderBy('ualias', 'ASC')

            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('ualias')
        ;

        $couldadd = ($activity != null);

        foreach ($qb->getQuery()->execute() as $statRow) {
            $curYear = $statRow['year'];

            $year = $this->getYear($years, $curYear, $couldadd);

            if ($year != null) {
                $month = $year->getOrAddMonth($statRow['month'], $couldadd);
                if ($month != null) {
                    $user = $month->getOrAddUser($statRow['ualias'], $couldadd);
                    if ($user != null) {
                        $func = "set" . ucwords($setter);
                        $user->$func($statRow['duration']);
                        if ($activity == 'Vacation') {
                        $year->sumVacationForUser($statRow['ualias'], $statRow['duration']);
                        }
                    }
                }
            }
        }

    }

    /**
     * @param $years
     * @param $curYear
     * @param $activity
     * @return array
     */
    public function getYear(&$years, $curYear, $couldadd): ?VacationYear
    {
        if (isset($years[$curYear])) {
            return $years[$curYear];
        }
        if ($couldadd) {
            $year = new VacationYear($curYear);
            for ($i = 12; $i > 0; $i--) {
                $month = $i < 10 ? '0' . $i : (string)$i;
                $year->setMonth(new VacationMonth($month, $curYear));
            }
            $years[$curYear] = $year;
            return $year;
        }
        return null;
    }
}
