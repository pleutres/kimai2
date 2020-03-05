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
use App\Event\TimesheetMetaDisplayEvent;
use App\Model\Invoice\Month;
use App\Model\Invoice\Year;
use App\Repository\UserInvoiceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin/userinvoice")
 * @Security("is_granted('view_other_timesheet')")
 */
class UserInvoiceController extends AbstractController
{


    /**
     * @Route(path="/", defaults={"page": 1}, name="invoice_user_admin", methods={"GET"})
     * @Security("is_granted('view_other_timesheet')")
     *
     * @param int $page
     * @param Request $request
     * @return Response
     */
    public function indexAction($page, Request $request)
    {
        return $this->index($page, $request, 'userinvoices/index.html.twig', TimesheetMetaDisplayEvent::TEAM_TIMESHEET);
    }

    protected function index($page, Request $request, string $renderTemplate, string $location): Response
    {


        $monthlyStats = $this->getMonthlyStatsWithFees();

        $viewVars = [
            'years' => $monthlyStats,
        ];

        return $this->render($renderTemplate, $viewVars);
    }


    /**
     * Returns an array of Year statistics.
     *
     * @param User|null $user
     * @param DateTime|null $begin
     * @param DateTime|null $end
     * @return Year[]
     */
    public function getMonthlyStatsWithFees(User $user = null, ?DateTime $begin = null, ?DateTime $end = null)
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        $qb->select('SUM(t.rate) as rate, SUM(t.duration) as duration, MONTH(t.begin) as month, YEAR(t.begin) as year, user.title as utitle, SUM(meta.value) as fees')
            ->from(Timesheet::class, 't')
            ->leftJoin('t.user', 'user')
            ->leftJoin('t.meta', 'meta')
        ;

        $qb->expr()->eq('meta.name', 'fees');

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
            ->addOrderBy('month', 'ASC')
            ->addOrderBy('utitle', 'ASC')

            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('utitle')
        ;

        $years = [];
        foreach ($qb->getQuery()->execute() as $statRow) {
            $curYear = $statRow['year'];

            if (!isset($years[$curYear])) {
                $year = new Year($curYear);
                for ($i = 1; $i < 13; $i++) {
                    $month = $i < 10 ? '0' . $i : (string) $i;
                    $year->setMonth(new Month($month));
                }
                $years[$curYear] = $year;
            }

            $month = $year->getOrAddMonth($statRow['month']);
            $user = $month->getOrAddUser($statRow['utitle']);
            $user->addStats($statRow['duration'], $statRow['duration'], is_null($statRow['fees'])?0.0:$statRow['fees']);

        }

        return $years;
    }
}
