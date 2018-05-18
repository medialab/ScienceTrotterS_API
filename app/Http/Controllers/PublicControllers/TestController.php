<?php

class TestController extends Controller
{
	public function list(Request $oRequest)
	{
		$aParcours = Parcours::where('state', true)->take($oRequest->input('limit'))->skip($oRequest->input('offset'))->get();
		return $this->sendResponse($aParcours->toArray(), null);
	}

	public function get($id) {
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null);
	}
}
