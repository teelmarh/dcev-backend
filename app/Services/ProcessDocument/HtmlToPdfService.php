<?php

namespace App\Services\ProcessDocument;

use App\Models\DocumentResourceTool;
use App\Traits\Image\AwsS3;
use chillerlan\QRCode\QRCode;
use DateTime;
use Illuminate\Support\Facades\Log;

class HtmlToPdfService
{
    use AwsS3;

    public function html($upload, $document)
    {
        // existing method unchanged
    }

    public function generateMembershipCardPdf($user)
    {
        $title = 'Membership Card - ' . $user->name;

        $profilePicUrl = $user->media ? $this->downloadFromS3($user->media->file_url) : asset('assets/images/default-profile.png');

        $memberSince = $user->created_at ? $user->created_at->format('M d, Y') : 'N/A';

        $stateChapter = $user->state_chapter ?? 'N/A';

        $memberId = $user->member_id ?? 'N/A';

        // Render the Blade view to HTML string
        $html = view('components.card.card', [
            'memberName' => $user->name,
            'profilePicUrl' => $profilePicUrl,
            'memberId' => $memberId,
            'stateChapter' => $stateChapter,
            'memberSince' => $memberSince,
        ])->render();

        $dir = (new FileStorageService)->folderPath($user);
        $fileDirHtml = $dir . rand(100000, 9000000) . '.html';
        $fileDirPdf = $dir . rand(100000, 9000000) . '.pdf';

        file_put_contents($fileDirHtml, $html);

        shell_exec("weasyprint $fileDirHtml $fileDirPdf");

        return $fileDirPdf;
    }

    public function dtcHtml($document)
    {
        $participantTd = '';

        $documentLogs = '';

        $bg = config('app.url').'assets/images/cert-bg.png';

        $url = config('app.url')."verify-document?id=$document->id";

        $data = '<img src="'.(new QRCode)->render($url).'" alt="QR Code" />';

        Log::debug('participant', ['participant' => $document->participants]);

        foreach ($document->participants as $participant) {

            $tool = DocumentResourceTool::where('user_id', $participant->user_id)->whereNotNull('append_print_id')->first();

            $userTool = $tool ? $this->toolCheckInPosition($tool) : 'No Signature';

            $role = $participant?->document?->user?->id === $participant->user_id ? 'Document Owner' : $participant->role;

            $participantTd .= '<tr style="border-color: inherit; border-style: solid; border-width: 0;">
                <td style="border-width: 1px; padding: 11.52px 32px">
                    <div style="display: flex; flex-wrap: wrap; padding: 11.52px 8px">
                        <div style="flex: 0 0 auto; width: 25%">'.$participant->first_name.' '.$participant->last_name.'</div>
                        <div style="flex: 0 0 auto; width: 75%">'.$role.'</div>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; padding: 11.52px 8px">
                        <div style="flex: 0 0 auto; width: 25%">Email</div>
                        <div style="flex: 0 0 auto; width: 75%; word-wrap: break-word">'.$participant->email.'</div>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; padding: 11.52px 8px">
                        <div style="flex: 0 0 auto; width: 25%">Device IP.</div>
                        <div style="flex: 0 0 auto; width: 75%">'.$participant?->user?->ip_address.'</div>
                    </div>
                </td>
                <td>
                    <div style="width: 100%; padding: 11.52px 8px">Signature</div>
                    <div style="width: 100%; padding: 11.52px 8px">
                        '.$userTool.'
                    </div>
                </td>
            </tr>';
        }

        $chunks = $document->documentLogs ? array_chunk($document->documentLogs->toArray(), 12) : [];
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $documentLogs .= '<div class="audit-trail" style="position: relative;">';

            $documentLogs .= '<div style="margin-top: 10px;">
                        <h5>Audit Trail - '.($index + 1).'</h5>
                      </div>';

            $documentLogs .= '<div class="card-body">
                        <ul class="timeline" style="padding: 0; margin-bottom: 0; margin-left: 1rem; list-style: none; font-size: 12px;">';

            foreach ($chunk as $log) {
                $originalTimestamp = $log['created_at'];
                $dateTime = new DateTime($originalTimestamp);
                $formattedTimestamp = $dateTime->format('M. jS, Y \a\t h:i:s a');
                $documentLogs .= '<li class="entry" style="position: relative; padding-left: 2.5rem; border-left: 1px solid #ebe9f1;">
                            <span style="position: absolute; left: -0.85rem; top: 0; z-index: 2; display: flex; justify-content: center; align-items: center; height: 1.75rem; width: 1.75rem; text-align: center; border-radius: 50%; border: 1px solid #003bb3; background-color: #fff;"></span>
                            <div style="position: relative; width: 100%; min-height: 4rem;">
                                <div>
                                    <small class="timestamp" style="font-size: 10px; color: #b9b9c3;">'.$formattedTimestamp.'</small>
                                </div>
                                <p class="detail">'.$log['log_name'].'</p>
                            </div>
                          </li>';
            }

            $documentLogs .= '</ul>
                    </div>';

            if ($index < $totalChunks - 1) {
                $documentLogs .= '</div><div style="position: relative; page-break-after: always;"></div><br>';
            } else {
                $documentLogs .= '</div>';
            }
        }

        $item = '<!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>'.$document->title.'</title>
        <style>
            @page { margin: 0; padding: 0; }
            @font-face {
                font-family: "Poppins";
                src: url("https://github.com/Fortiz2305/webpage/blob/master/fonts/poppins/poppins-regular-webfont.ttf")
                font-weight: normal;
                font-style: normal;
            }
            body {
                font-family: "Poppins", sans-serif;
            }
        </style>
        </head>
        <body style="margin: 0; padding: 0;">
            <div style="background-image: url('.$bg.'); background-repeat: no-repeat; background-size: 100% 100%; height: 1059px;">
                <div style="padding: 24px">
                    <div style="text-align: center">
                        <h1 style="font-weight: bolder">Digital Transaction Certificate</h1>
                        <p>Document Ref: '.$document->id.'</p>
                    </div>
                    <div>
                        <p style="font-weight: bold; margin: 16px 0">Document Name: '.$document->title.'</p>
                        <p style="font-weight: bold; margin: 16px 0">Document Completed On: '.$document->updated_at.' </p>
                    </div>

                    <table style="border: 1px solid #4b4b4b; border-collapse: collapse; color: #6e6b7b; width: 100%; vertical-align: middle;" cellpadding="0" cellspacing="0">
                        '.$participantTd.'
                    </table>

                    <div style="margin: 32px 0">
                        <h2>How to verify this document:</h2>
                        <p>
                        1. Visit www.gettonote.com/verify-document and type in the
                        document id above in the search panel Or.
                        </p>
                        <p>2. Scan the QR Code at the tale end using your mobile device.</p>
                        <div style="position: absolute; bottom: 20px; right: 20px; width: 100px;">
                               '.$data.'
                        </div>
                    </div>
                </div>
            </div>
        </body>';

        $auditTrail = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Audit Trail</title>
                <style>
                @page {
                    margin: 0px;
                }
                @font-face {
                    font-family: "Poppins";
                    src: url("https://github.com/Fortiz2305/webpage/blob/master/fonts/poppins/poppins-regular-webfont.ttf")
                    font-weight: normal;
                    font-style: normal;
                }
                body {
                    font-family: "Poppins", sans-serif;
                }
                </style>
            </head>
            <body>
                <div>
                    <div style="padding: 24px">
                        '.$documentLogs.'
                    </div>
                </div>
            </body>
        </html>';

        $dir = (new FileStorageService)->folderPath($document);
        $fileDirHtml1 = $dir.rand(100000, 9000000).'_page1.html';
        file_put_contents($fileDirHtml1, $item);

        $fileDirHtml2 = $dir.rand(100000, 9000000).'_page2.html';
        file_put_contents($fileDirHtml2, $auditTrail);

        $fileDirPdf1 = $dir.rand(100000, 9000000).'.pdf';
        $fileDirPdf2 = $dir.rand(100000, 9000000).'.pdf';

        shell_exec("weasyprint $fileDirHtml1 $fileDirPdf1");
        shell_exec("weasyprint $fileDirHtml2 $fileDirPdf2");

        // shell_exec("wkhtmltopdf --dpi 96 --disable-smart-shrinking -T 0 -B 0 -L 0 -R 0 $fileDirHtml1 $fileDirHtml2 $fileDirPdf");

        return [$fileDirPdf1, $fileDirPdf2];
    }

    public function toolCheckInPosition($tool, $position = false)
    {
        $setTool = '';

        if ((($tool->type == 'Text' || $tool->type == 'Fullname' || $tool->type == 'Date') && ($tool?->value != null || $tool?->value != ''))) {
            $x = $tool->tool_pos_left;
            $y = $tool->tool_pos_top;
            $width = ($tool->tool_width);
            $height = ($tool->tool_height);
            if ($position == true) {
                $setTool .= '
                    <div style="font-size: 14px; position: absolute; top: '.$y.'px; left: '.$x.'px; z-index: 1; width: '.$width.'px; height: '.$height.'px !important; color:black;">
                        '.($tool->value).'
                    </div>';
            } else {
                $setTool .= '
                    <div style="font-size: 14px; position: absolute; z-index: 1; width: '.$width.'px; height: '.$height.'px !important; color:black;">
                        '.$tool->value.'
                    </div>';
            }
        }

        if ((($tool?->type == 'CompanySeal' ||
                $tool?->type == 'CompanyStamp' ||
                $tool?->type == 'NotaryStamp' ||
                $tool?->type == 'NotaryTraditionalSeal' ||
                $tool?->type == 'Signature' ||
                $tool?->type == 'Initial' ||
                $tool?->type == 'Photograph' ||
                $tool?->type == 'Photo' ||
                $tool?->type == 'Seal' ||
                $tool?->type == 'Stamp') &&
            ($tool?->value != null || $tool?->value != ''))) {

            $fileTool = $this->downloadFromS3($tool->value);
            $x = $tool->tool_pos_left;
            $y = $tool->tool_pos_top;
            $width = ($tool->tool_width);
            $height = ($tool->tool_height);

            if ($position) {
                $setTool .= '
                    <div style="position: absolute; top: '.$y.'px; left: '.$x.'px; z-index: 1; width: '.$width.'px; height: '.$height.'px;">
                        <img src="'.$fileTool.'" style="object-fit: scale-down; height: 100%;">
                    </div>';
            } else {
                $setTool .= '
                    <div style="position: absolute; z-index: 1; width: '.$width.'px; height: '.$height.'px;">
                        <img src="'.$fileTool.'" style="object-fit: scale-down; height: 100%;">
                    </div>';
            }

        }

        return $setTool;
    }
}
