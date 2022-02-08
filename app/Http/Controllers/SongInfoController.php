<?php

namespace App\Http\Controllers;

use App\Song;
use App\SongInfo;
use App\Traits\GoogleClient;
use App\Traits\InformAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SongInfoController extends Controller
{
    use InformAdmin,
        GoogleClient;

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
    public function index(): View
    {
    	$songInfo = SongInfo::with('song')->get();
        return view('songs.info.index', compact('songInfo'));
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
    final public function create(): View
    {
    	$songs = auth()->user()->songs()->with('info')->get();
        return view('songs.info.create', compact('songs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    final public function store(Request $request): RedirectResponse
    {
        if ($songId = $request->get('song_id')) {
        	$song = Song::find($songId);
        	if ($song) {
        		$song->info()->create($request->only((new SongInfo())->getFillable()));

        		$this->userActivity(
        		    'Added song splits',
                    auth()->user()->name . ' Added splits for song "' . $song->name . '"',
                    [],
                    $this->createLink('folders', $song->folder_id)
                );

                $file = \PDF::setOptions(['defaultFont' => 'sans-serif'])
                    ->loadView('emails.splits', ['song' => $song])
                    ->stream('test.pdf');

                $this->createFileOrFolder(
                    "splits",
                    '',
                    $song->folder_id,
                    '',
                    [
                        'data' => $file,
                        'mimeType' => 'application/pdf',
                        'uploadType' => 'resumable'
                    ],
                    false,
                    true
                );
	        }
        }
        return redirect()->route('song-info.create');
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param SongInfo $songInfo
	 * @return View
	 */
    final public function edit(SongInfo $songInfo): View
    {
        return view('songs.info.edit', compact('songInfo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SongInfo $songInfo
     * @return RedirectResponse
     * @throws \Exception
     */
    final public function update(Request $request, SongInfo $songInfo): RedirectResponse
    {
    	$songInfo->update($request->only((new SongInfo())->getFillable()));

        $this->userActivity(
            'Update song splits',
            auth()->user()->name . ' updated splits for song "' . $songInfo->song->name . '"',
            [],
            $this->createLink('folders', $songInfo->song->folder_id)
        );

        $file = \PDF::setOptions(['defaultFont' => 'sans-serif'])
            ->loadView('emails.splits', ['song' => $songInfo->song])
            ->stream('test.pdf');

        $this->createFileOrFolder(
            "splits",
            '',
            $songInfo->song->folder_id,
            '',
            [
                'data' => $file,
                'mimeType' => 'application/pdf',
                'uploadType' => 'resumable'
            ],
            false,
            true
        );
        return redirect()->route('song-info.create');
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
}
