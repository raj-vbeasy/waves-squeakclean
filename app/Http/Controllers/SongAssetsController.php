<?php

namespace App\Http\Controllers;

use App\Song;
use App\SongAssets;
use App\Traits\GoogleClient;
use App\Traits\InformAdmin;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SongAssetsController extends Controller
{
	use GoogleClient,
        InformAdmin;

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
    final public function index(): View
    {
    	$songs = auth()->user()->songs()->with('assets')->has('assets')->get();
        return view('songs.assets.index', compact('songs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
	    $songs = auth()->user()->songs()->with('assets')->get();
	    return view('songs.assets.create', compact('songs'));
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Exception
	 */
    final public function store(Request $request): JsonResponse
    {
    	if ($songId = $request->get('song_id')) {
    		$song = Song::find($songId);
    		if ($song) {
    			$insertArr = [];
    			if ($file = $request->file('full_track')) {
    				$insertArr['full_track'] = $this->createFileOrFolder(
    					'Full Track - ' . $file->getClientOriginalName(),
					    '',
					    $song->folder_id,
					    '',
					    [
						    'data' => file_get_contents($file->getRealPath()),
						    'mimeType' => $file->getMimeType(),
						    'uploadType' => 'resumable'
					    ]
				    );
			    }

			    if ($file = $request->file('instrumental')) {
				    $insertArr['instrumental'] = $this->createFileOrFolder(
					    'Instrumental - ' . $file->getClientOriginalName(),
					    '',
					    $song->folder_id,
					    '',
					    [
						    'data' => file_get_contents($file->getRealPath()),
						    'mimeType' => $file->getMimeType(),
						    'uploadType' => 'resumable'
					    ]
				    );
			    }

			    if ($file = $request->file('clean')) {
				    $insertArr['clean'] = $this->createFileOrFolder(
					    'Clean - ' . $file->getClientOriginalName(),
					    '',
					    $song->folder_id,
					    '',
					    [
						    'data' => file_get_contents($file->getRealPath()),
						    'mimeType' => $file->getMimeType(),
						    'uploadType' => 'resumable'
					    ]
				    );
			    }

			    if ($file = $request->file('steam')) {
				    $insertArr['steam'] = $this->createFileOrFolder(
					    'Steam - ' . $file->getClientOriginalName(),
					    '',
					    $song->folder_id,
					    '',
					    [
						    'data' => file_get_contents($file->getRealPath()),
						    'mimeType' => $file->getMimeType(),
						    'uploadType' => 'resumable'
					    ]
				    );
			    }

			    $song->assets()->create($insertArr);

                $this->userActivity(
                    'Added song assets',
                    auth()->user()->name . ' added assets for song "' . $song->name . '"',
                    array_map([$this, 'formatKey'], array_keys($insertArr)),
                    $this->createLink('folders', $song->folder_id)
                );
		    }
	    }

	    return response()->json(['status' => true]);
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return Application|Factory|RedirectResponse|Response|View
	 */
    final public function edit($id)
    {
    	if ($assets = SongAssets::find($id)) {
		    return view('songs.assets.edit', compact('assets'));
	    }

    	return redirect()->back();
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return RedirectResponse
	 * @throws Exception
	 */
    public function update(Request $request, $id): JsonResponse
    {
        set_time_limit(300000);
	    if ($asset = SongAssets::find($id)) {
	    	$updateArr = [];
			if ($file = $request->file('full_track')) {
				$updateArr['full_track'] = $this->createFileOrFolder(
					'Full Track - ' . $file->getClientOriginalName(),
					'',
					$asset->song->folder_id,
					'',
					[
						'data' => file_get_contents($file->getRealPath()),
						'mimeType' => $file->getMimeType(),
						'uploadType' => 'resumable'
					]
				);
				if (!empty($asset->full_track)) {
					$this->deleteFileOrFolder($asset->full_track);
				}
			}

			if ($file = $request->file('instrumental')) {
				$updateArr['instrumental'] = $this->createFileOrFolder(
					'Instrumental - ' . $file->getClientOriginalName(),
					'',
					$asset->song->folder_id,
					'',
					[
						'data' => file_get_contents($file->getRealPath()),
						'mimeType' => $file->getMimeType(),
						'uploadType' => 'resumable'
					]
				);
				if (!empty($asset->instrumental)) {
					$this->deleteFileOrFolder($asset->instrumental);
				}
			}

			if ($file = $request->file('clean')) {
				$updateArr['clean'] = $this->createFileOrFolder(
					'Clean - ' . $file->getClientOriginalName(),
					'',
					$asset->song->folder_id,
					'',
					[
						'data' => file_get_contents($file->getRealPath()),
						'mimeType' => $file->getMimeType(),
						'uploadType' => 'resumable'
					]
				);
				if (!empty($asset->clean)) {
					$this->deleteFileOrFolder($asset->clean);
				}
			}

			if ($file = $request->file('steam')) {
				$updateArr['steam'] = $this->createFileOrFolder(
					'Steam - ' . $file->getClientOriginalName(),
					'',
					$asset->song->folder_id,
					'',
					[
						'data' => file_get_contents($file->getRealPath()),
						'mimeType' => $file->getMimeType(),
						'uploadType' => 'resumable'
					]
				);

				if (!empty($asset->steam)) {
					$this->deleteFileOrFolder($asset->steam);
				}
			}

			$asset->update($updateArr);

            $this->userActivity(
                'Updated song assets',
                auth()->user()->name . ' updated assets for song "' . $asset->song->name . '"',
                array_map([$this, 'formatKey'], array_keys($updateArr)),
                $this->createLink('folders', $asset->song->folder_id)
            );
	    }

	    return response()->json(['status' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


    private function formatKey($str)
    {
        return ucwords(str_ireplace('_', ' ', $str));
    }
}
