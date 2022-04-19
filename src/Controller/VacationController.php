<?php


namespace App\Controller;

use App\Entity\Timesheet;
use App\Entity\User;
use App\Export\ServiceExport;
use App\Model\UserVacation\VacationMonth;
use App\Model\UserVacation\VacationYear;
use App\Repository\TimesheetRepository;
use App\Repository\UserInvoiceRepository;
use App\Export\UserInvoice\XlsxRenderer;
use App\Repository\UserRepository;
use DateTime;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin/uservacation")
 * @Security("is_granted('view_activity')")
 */
class VacationController extends AbstractController
{

    private $renderer;
    private $logger;
    private $userRepository;

    /**
     * @param TimesheetRepository $timesheet
     * @param ServiceExport $export
     */
    public function __construct(XlsxRenderer $renderer, LoggerInterface $logger = null, UserRepository $repository)
    {
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->userRepository = $repository;
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
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2021-06-29 00:00:00');
        $user = $this->userRepository->loadUserbyUsername('simon');
        $this->getMonthlyActivity($years, 'vacation', 'Vacation', 'Vacation', $user, $startDate);
        $this->getMonthlyActivity($years, 'rtt', 'Vacation','RTT', $user, $startDate);

        $yearsGlobal = [];
        $this->getMonthlyActivity($yearsGlobal, 'total', null, null, $user, $startDate);

        $viewVars = [
            'years' => $years,
            'yearsGlobal' => $years,
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
    public function getMonthlyActivity(&$years, $setter, $project = null, $activity = null, User $user = null, ?DateTime $begin = null, ?DateTime $end = null)
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        $qb->select('SUM(t.duration) as duration, MONTH(t.begin) as month, YEAR(t.begin) as year, DAY(t.begin) as day, user.alias as ualias')
            ->from(Timesheet::class, 't')
            ->leftJoin('t.user', 'user')
            ->leftJoin('t.activity', 'activity')
            ->leftJoin('t.project', 'project')
        ;

//         $qb->andWhere()->eq('', 'Vacation');
//         $qb->andWhere()->eq('project.name', ('Vacation');
        if ($activity != null) {
            $qb->andWhere('activity.name = :activity')->setParameter('activity', $activity);
            $qb->andWhere('project.name = :project')->setParameter('project', $project);
        }
        $qb->andWhere('activity.name <> :publichd')->setParameter('publichd', 'Public holiday');
        $qb->andWhere('t.duration > 0');

        if (!empty($begin)) {
            $qb->andWhere($qb->expr()->gte('t.begin', ':from'))
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
            ->addOrderBy('day', 'DESC')
            ->addOrderBy('ualias', 'ASC')

            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('day')
            ->addGroupBy('ualias')
        ;



        $couldadd = true; // ($activity != null);

        foreach ($qb->getQuery()->execute() as $statRow) {
            $curYear = $statRow['year'];

            $year = $this->getYear($years, $curYear, $couldadd);

            if ($year == null) {
                continue;
            }
            $month = $year->getOrAddMonth($statRow['month'], $couldadd);
            if ($month == null) {
                continue;
            }
            $day = $month->getOrAddDay($statRow['day'], $couldadd);
            if ($day == null) {
                continue;
            }
            $user = $day->getOrAddUser($statRow['ualias'], $couldadd);
            if ($user != null) {
                $func = "set" . ucwords($setter);
                $user->$func($statRow['duration']);
                if ($activity == 'Vacation' || $activity == 'RTT') {
                    $year->sumVacationForUser($statRow['ualias'], $statRow['duration']);
                    $month->sumVacationForUser($statRow['ualias'], $statRow['duration']);
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
