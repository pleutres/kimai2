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
use App\Model\UserInvoice\Month;
use App\Model\UserInvoice\Year;
use App\Repository\TimesheetRepository;
use App\Repository\UserInvoiceRepository;
use App\Export\UserInvoice\XlsxRenderer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin/userinvoice")
 * @Security("is_granted('view_activity')")
 */
class UserInvoiceController extends AbstractController
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
     * @Route(path="/", defaults={"page": 1}, name="invoice_user_admin", methods={"GET"})
     * @Security("is_granted('view_other_timesheet')")
     *
     * @param Request $request
     * @return Response
     */
    public function index($page, Request $request)
    {

        $monthlyStats = $this->getMonthlyStatsWithFees();

        $viewVars = [
            'years' => $monthlyStats,
        ];

        return $this->render('userinvoices/index.html.twig', $viewVars);
    }

    /**
     * @Route(path="/export", name="invoice_user_admin_export", methods={"GET"})
     * @Security("is_granted('view_other_timesheet')")
     *
     * @param Request $request
     * @return Response
     */
    public function export(Request $request)
    {
        $monthlyStats = $this->getMonthlyStatsWithFees();

        return $this->renderer->renderUserInvoice($monthlyStats, null);
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

        $qb->select('SUM(t.rate) as rate, SUM(t.duration) as duration, MONTH(t.begin) as month, YEAR(t.begin) as year, user.alias as ualias, SUM(meta.value) as fees')
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
            ->addOrderBy('month', 'DESC')
            ->addOrderBy('ualias', 'ASC')

            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('ualias')
        ;

        $years = [];

        foreach ($qb->getQuery()->execute() as $statRow) {
            $curYear = $statRow['year'];

            if (!isset($years[$curYear])) {
                $year = new Year($curYear);
                for ($i = 12; $i > 0 ; $i--) {
                    $month = $i < 10 ? '0' . $i : (string) $i;
                    $year->setMonth(new Month($month, $curYear));
                }
                $years[$curYear] = $year;
            }

            $month = $year->getOrAddMonth($statRow['month']);
            $user = $month->getOrAddUser($statRow['ualias']);
            $user->addStats($statRow['rate'], $statRow['duration'], is_null($statRow['fees'])?0.0:$statRow['fees']);

        }

        return $years;
    }
}
