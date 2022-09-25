<?php

namespace DTApi\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends BaseController
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //$user_id is undefined variable
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif($request->__authenticatedUser->user_type == config('general.admin_role_id') || $request->__authenticatedUser->user_type == config('general.super_admin_role_id')) {
            //Use a proper function name as getAllUserJobs
            $response = $this->repository->getAllUserJobs($request);
        }

        return $this->sendResponse($response->toArray(), 'Data retrieved successfully');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);
        return $this->sendResponse($job, 'Data retrieved successfully');
    }

    /**
     * @param CreateBookingRequest $request
     * @return mixed
     */
    public function store(CreateBookingRequest $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return $this->sendResponse($response, 'Data saved successfully');

    }

    /**
     * @param $id
     * @param UpdateBookingRequest $request
     * @return mixed
     */
    public function update($id, UpdateBookingRequest $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return $this->sendResponse($response, 'Data updated successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return $this->sendResponse($response, 'Email send successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return $this->sendResponse($response, 'Data retrieved successfully');
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return $this->sendResponse($response, 'Job accepted successfully');
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return $this->sendResponse($response, 'Job accepted successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return $this->sendResponse($response, 'Job cancelled successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return $this->sendResponse($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return $this->sendResponse($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return $this->sendResponse($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = (empty($data['distance'])) ? '' : $data['distance'];
        $time = (empty($data['time'])) ? '' : $data['time'];
        $jobid = (empty($data['jobid'])) ? null : $data['jobid'];
        $session = (empty($data['session_time'])) ? '' : $data['session_time'];
        $manually_handled = ($data['manually_handled'] == 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] == 'true') ? 'yes' : 'no';
        $admincomment = (empty($data['admincomment'])) ? '' : $data['admincomment'];

        if ($data['flagged'] == 'true') {
            if($admincomment == '') return "Please, add comment";
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($time || $distance) {
            // Queries should move to respective repositories and just call the repository function
            $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            // Queries should move to respective repositories and just call the repository function
            $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }

        return $this->sendResponse([], 'Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return $this->sendResponse($response, 'Data retrieved successfully');
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return $this->sendResponse([], 'Push sent');
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return $this->sendResponse([], 'SMS sent');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getCode());
        }
    }

}
