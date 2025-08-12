<?php
include "api.php";

// Always return JSON to the AJAX call
header('Content-Type: application/json');

// Capture radio selection
$selectedOption = $_POST['radio'] ?? null;

// Validate file upload
if (!isset($_FILES['myFile'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
    exit;
}

// Read uploaded file
$fileTmpPath = $_FILES['myFile']['tmp_name'];
$jsonString = file_get_contents($fileTmpPath);
$data = json_decode($jsonString, true);

// Route based on radio selection
switch ($selectedOption) {
    case 'message-data':
        $messageData = processMessageData($data);
        $totalsData = processVoiceLoveTotals($data);
        
        // Post messages and totals to REDCap
        if (!empty($messageData) && !isset($messageData['error'])) {
            postForm($messageData);
        }
        if (!empty($totalsData) && !isset($totalsData['error'])) {
            postForm($totalsData);
        }
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'type' => 'message-data',
            'data' => [
                'messages' => $messageData,
                'totals' => $totalsData
            ]
        ]);
        break;

    case 'channel-data':
        $channelData = processChannelData($data);
        if (!isset($channelData['error'])) {
            // post per-channel repeats
            postForm($channelData['channels']);
            // post totals separately
            postForm($channelData['totals']);
        }
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'type' => 'channel-data',
            'data' => $channelData
        ]);
        break;

    case 'unique-senders':
        $uniqueSenderData = processUniqueSenders($data);
        if (!isset($uniqueSenderData['error'])) {
            postForm($uniqueSenderData);
        }
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'type' => 'unique-senders',
            'data' => $uniqueSenderData
        ]);
        break;

    default:
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing radio selection']);
        break;
}

// ===== Helper Functions =====

function processMessageData(array $data) {
    $study = $data[0] ?? null;
    if (!$study) {
        return ['error' => 'Invalid data format'];
    }

    $record_id = $study['studyId'];
    $repeat_instance_start = 1;

    list($from_patient_arr, $next_instance) = buildMessageArray(
        $study['messagesFromPatient'], 1, $record_id, $repeat_instance_start
    );
    list($to_patient_arr, $_) = buildMessageArray(
        $study['messagesToPatient'], 2, $record_id, $next_instance
    );

    return array_merge($from_patient_arr, $to_patient_arr);
}

function buildMessageArray(array $messages, int $msg_type, $record_id, int $start_instance) {
    $result = [];
    $instance = $start_instance;

    foreach ($messages as $msg) {
        $base = [
            "msg_type"  => $msg_type,
            "msg_id"    => $msg['messageId'],
            "dur"       => $msg['durationInSeconds'],
            "reort_msg" => $msg['isReorientation'],
        ];

        if (!empty($msg['messagesPlayed'])) {
            foreach ($msg['messagesPlayed'] as $play) {
                $result[] = array_merge(getBaseRow($record_id, $instance++, $base), [
                    "msg_event_id"                    => $play['id'],
                    "played_dttm"                     => $play['timePlayed'],
                    "voicelove_message_data_complete" => 2,
                ]);
            }
        } else {
            $result[] = array_merge(getBaseRow($record_id, $instance++, $base), [
                "msg_event_id"                    => null,
                "played_dttm"                     => null,
                "voicelove_message_data_complete" => 2,
            ]);
        }
    }

    return [$result, $instance];
}

function getBaseRow($record_id, $instance, array $base) {
    return array_merge([
        "record_id"                => $record_id,
        "redcap_event_name"        => "one_time_formsenro_arm_1",
        "redcap_repeat_instrument" => "voicelove_message_data",
        "redcap_repeat_instance"   => $instance,
    ], $base);
}

function processChannelData(array $data) {
    $study = $data[0] ?? null;
    if (!$study || empty($study['numberOfPeopleInChannels'])) {
        return ['error' => 'Invalid channel data format'];
    }

    $record_id = $study['studyId'];
    $instance = 1; // start repeat instance for per-channel records

    // Build per-channel repeat form rows
    $rows = [];
    foreach ($study['numberOfPeopleInChannels'] as $channel) {
        $rows[] = [
            "record_id"                       => $record_id,
            "redcap_event_name"               => "one_time_formsenro_arm_1",
            "redcap_repeat_instrument"        => "voicelove_channel_data",
            "redcap_repeat_instance"          => $instance++,
            "channel_id"                      => $channel['channelId'],
            "peep_num"                       => $channel['numberOfPeople'],
            "voicelove_channel_data_complete" => 2
        ];
    }

    // Add totals data for numberOfChannels as a separate form/instance
    $totals = [
        [
            "record_id"                      => $record_id,
            "redcap_event_name"              => "one_time_formsenro_arm_1",
            "chan_num"                      => $study['numberOfChannels'] ?? null,
        ]
    ];

    return [
        'channels' => $rows,
        'totals' => $totals
    ];
}

function processUniqueSenders(array $data) {
    $study = $data[0] ?? null;
    if (!$study || !isset($study['numberOfUniqueSenders'])) {
        return ['error' => 'Invalid unique senders data format'];
    }

    $record_id = $study['studyId'];

    return [
        [
            "record_id"          => $record_id,
            "redcap_event_name"  => "one_time_formsenro_arm_1",
            "un_send"            => $study['numberOfUniqueSenders'],
        ]
    ];
}

function processVoiceLoveTotals(array $data) {
    $study = $data[0] ?? null;
    if (!$study) {
        return ['error' => 'Invalid data format for totals'];
    }

    $record_id = $study['studyId'];

    return [
        [
            "record_id"                         => $record_id,
            "redcap_event_name"                 => "one_time_formsenro_arm_1",
            "unique_from_pt"                    => $study['numberOfUniqueMessagesFromPatient'] ?? null,
            "played_msg_from_pt"                => $study['numberOfPlayedMessagesFromPatient'] ?? null,
            "dur_play_msg_from_pt"              => $study['durationOfPlayedMessagesFromPatient'] ?? null,
            "unique_to_pt"                      => $study['numberOfUniqueMessagesToPatient'] ?? null,
            "played_msg_to_pt"                  => $study['numberOfPlayedMessagesToPatient'] ?? null,
            "num_played_reornt_to_pt"           => $study['numberOfPlayedReorientationToPatient'] ?? null,
            "dur_play_msg_to_pt"                => $study['durationOfPlayedMessagesToPatient'] ?? null,
        ]
    ];
}

?>
