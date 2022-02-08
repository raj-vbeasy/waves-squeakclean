<?php

namespace App\Http\Controllers;

use App\PromoAssets;
use App\Traits\GoogleClient;
use App\Traits\InformAdmin;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PromoAssetsController extends Controller
{
	use GoogleClient,
        InformAdmin;

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    final public function create(): View
    {
    	$assets = auth()->user()->promoAssets;
        return view('promo-assets.index', compact('assets'));
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Exception
	 */
    final public function save(Request $request): RedirectResponse
    {
    	$promoAssets = auth()->user()->promoAssets;
	    $arr = [];
	    $updateKeys = [];
    	if (empty($promoAssets)) {
		    $arr['folder_id'] = $this->createFileOrFolder(
			    'Promo Assets',
			    'application/vnd.google-apps.folder',
			    auth()->user()->folder_id,
			    '',
			    [],
			    false
		    );
	    } else {
    		$arr['folder_id'] = $promoAssets->folder_id;
	    }

    	if ($bio = $request->file('bio')) {
		    $arr['bio'] = $this->createFileOrFolder(
			    'Bio - ' . $bio->getClientOriginalName(),
			    '',
			    $arr['folder_id'],
			    '',
			    [
				    'data' => file_get_contents($bio->getRealPath()),
				    'mimeType' => $bio->getMimeType(),
				    'uploadType' => 'media'
			    ]
		    );
            $updateKeys[] = 'Bio';
		    if (!empty($promoAssets) && !empty($promoAssets->bio)) {
			    $this->deleteFileOrFolder($promoAssets->bio);
		    }
	    }

	    if ($oneSheet = $request->file('one_sheet')) {
		    $arr['one_sheet'] = $this->createFileOrFolder(
			    'One Sheet - ' . $oneSheet->getClientOriginalName(),
			    '',
			    $arr['folder_id'],
			    '',
			    [
				    'data' => file_get_contents($oneSheet->getRealPath()),
				    'mimeType' => $oneSheet->getMimeType(),
				    'uploadType' => 'media'
			    ]
		    );
            $updateKeys[] = 'One Sheet';
		    if (!empty($promoAssets) && !empty($promoAssets->one_sheet)) {
			    $this->deleteFileOrFolder($promoAssets->one_sheet);
		    }
	    }

	    if ($promoPics = $request->file('promo_pics')) {
		    foreach ($promoPics as $promoPic) {
	    		$arr['promo_pics'][] = $this->createFileOrFolder(
				    'Promo Pics - ' . $promoPic->getClientOriginalName(),
				    '',
				    $arr['folder_id'],
				    '',
				    [
					    'data' => file_get_contents($promoPic->getRealPath()),
					    'mimeType' => $promoPic->getMimeType(),
					    'uploadType' => 'media'
				    ]
			    );
		    }
            $updateKeys[] = 'Promo Pics';
		    if (!empty($promoAssets) && !empty($promoAssets->full_track)) {
		    	foreach ($promoAssets->promo_pics as $promoPic) {
				    $this->deleteFileOrFolder($promoPic);
			    }
		    }
	    }

	    if (empty($promoAssets)) {
		    auth()->user()->promoAssets()->create($arr);
		    $subject = 'Added promo assets';
		    $heading = auth()->user()->name . ' added promo assets';
	    } else {
		    $promoAssets->update($arr);
            $subject = 'Updated promo assets';
            $heading = auth()->user()->name . ' updated promo assets';
	    }

        $this->userActivity(
            $subject,
            $heading,
            $updateKeys,
            $this->createLink('folders', $arr['folder_id'])
        );

        return redirect()->route('promo-assets');
    }
}
