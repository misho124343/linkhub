<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$my_id = (int)$_SESSION['user_id'];
?>

<style>
    .btn-call-end:hover {
        transform: scale(1.15) !important;
    }
</style>

<div class="container" id="main-video-container">
    <h1 class="chat-title video-page-title">MEET</h1>

    <div id="room-selection" class="call-overlay">
        <h2 style="margin-bottom: 10px; font-weight: 900;">Enter a room</h2>
        <p style="margin-bottom: 25px; color: var(--text-muted); font-size: 14px;">
            Create a room or join an existing one
        </p>

        <div id="join-btn" style="display: none;">
            <button onclick="createRoom()" class="btn-primary" style="max-width: 250px; margin-bottom: 12px;">
                Create room
            </button>

            <button onclick="joinRoom()" class="btn-primary" style="max-width: 250px;">
                Join room
            </button>
        </div>

        <p id="status-msg" style="margin-top: 15px; color: var(--text-muted); font-weight: 600;">
            Wait for the camera...
        </p>
    </div>

    <div id="video-grid" class="video-grid" style="display: none;"></div>

    <div id="call-controls" class="call-controls" style="display: none;">
        <button class="btn-call btn-call-action" onclick="toggleAudio()" id="mic-btn" title="Microphone">
            <i class="fa-solid fa-microphone"></i>
        </button>

        <button class="btn-call btn-call-action" onclick="toggleVideo()" id="cam-btn" title="Camera">
            <i class="fa-solid fa-video"></i>
        </button>

        <button class="btn-call btn-call-action" onclick="toggleScreenShare()" id="screen-btn" title="Share screen">
            <i class="fa-solid fa-desktop"></i>
        </button>

        <button class="btn-call btn-call-action" onclick="toggleFullScreen()" id="fullscreen-btn" title="Full screen">
            <i class="fa-solid fa-expand"></i>
        </button>

        <button class="btn-call btn-call-rec" onclick="toggleRecording()" id="rec-btn" title="Record">
            <i class="fa-solid fa-circle"></i>
        </button>

        <button class="btn-call btn-call-end" onclick="endCallAndCheckRecord()">
            <i class="fa-solid fa-phone-slash"></i>
        </button>
    </div>
</div>

<script src="https://unpkg.com/peerjs@1.4.7/dist/peerjs.min.js"></script>

<script>
let myStream;
let peer;
let mediaRecorder;
let screenStream;
let recordingStream;

let recordedChunks = [];
let isRecording = false;
let isSharingScreen = false;
let currentRoom = null;

const myId = <?php echo $my_id; ?>;
const videoGrid = document.getElementById('video-grid');
const joinBtn = document.getElementById('join-btn');
const statusMsg = document.getElementById('status-msg');

const peerConfig = {
    config: {
        iceServers: [
            { urls: "stun:stun.l.google.com:19302" },
            { urls: "stun:stun1.l.google.com:19302" }
        ]
    }
};

navigator.mediaDevices.getUserMedia({
    video: true,
    audio: true
})
.then(function(stream) {
    myStream = stream;
    statusMsg.innerText = "The camera is ready. Create or join a room.";
    joinBtn.style.display = 'block';
})
.catch(function(err) {
    console.error("Camera error:", err);
    statusMsg.innerText = "Camera and microphone access are needed.";
});

function getRoomNumber() {
    const room = prompt("Enter room number:");

    if (!room || room.trim() === "") {
        return null;
    }

    return room.trim();
}

function startVideoInterface() {
    document.getElementById('room-selection').style.display = 'none';
    videoGrid.style.display = 'grid';
    document.getElementById('call-controls').style.display = 'flex';

    addVideoStream('local', myStream, 'Me');
}

function createPeer(peerId) {
    let createdPeer;

    if (peerId) {
        createdPeer = new Peer(peerId, peerConfig);
    } else {
        createdPeer = new Peer(null, peerConfig);
    }

    createdPeer.on('call', function(call) {
        call.answer(myStream);

        call.on('stream', function(stream) {
            addVideoStream(call.peer, stream, 'User');
        });

        call.on('close', function() {
            removeVideo(call.peer);
        });

        call.on('error', function(err) {
            console.log("Call error:", err);
        });
    });

    createdPeer.on('error', function(err) {
        console.log("PeerJS error:", err);

        if (err.type === 'unavailable-id') {
            alert("This room already exists. Use Join room instead.");
        } else {
            alert("Video connection error: " + err.type);
        }
    });

    return createdPeer;
}

function createRoom() {
    currentRoom = getRoomNumber();

    if (!currentRoom) {
        return;
    }

    const hostPeerId = "linkhub_room_" + currentRoom + "_host";

    startVideoInterface();

    peer = createPeer(hostPeerId);

    peer.on('open', function(id) {
        console.log("Room created:", id);
        statusMsg.innerText = "Room created: " + currentRoom;
        alert("Room created. Tell the other user to join room: " + currentRoom);
    });
}

function joinRoom() {
    currentRoom = getRoomNumber();

    if (!currentRoom) {
        return;
    }

    const hostPeerId = "linkhub_room_" + currentRoom + "_host";
    const guestPeerId = "linkhub_room_" + currentRoom + "_user_" + myId + "_" + Date.now();

    startVideoInterface();

    peer = createPeer(guestPeerId);

    peer.on('open', function() {
        const call = peer.call(hostPeerId, myStream);

        if (!call) {
            alert("Could not start the call.");
            return;
        }

        call.on('stream', function(stream) {
            addVideoStream(hostPeerId, stream, "Room " + currentRoom);
        });

        call.on('close', function() {
            removeVideo(hostPeerId);
        });

        call.on('error', function(err) {
            console.log("Call error:", err);
            alert("Could not connect to the room. Make sure the room is already created.");
        });
    });
}

function addVideoStream(id, stream, labelText) {
    if (document.getElementById('div_' + id)) {
        return;
    }

    const div = document.createElement('div');
    div.id = 'div_' + id;
    div.className = 'video-wrapper';

    const video = document.createElement('video');
    video.srcObject = stream;
    video.playsInline = true;
    video.autoplay = true;

    if (id === 'local') {
        video.muted = true;
    }

    video.onloadedmetadata = function() {
        video.play().catch(function(err) {
            console.log("Video play blocked:", err);
        });
    };

    const label = document.createElement('span');
    label.className = 'video-label';
    label.innerText = labelText;

    div.append(video, label);
    videoGrid.append(div);
}

function removeVideo(id) {
    const videoBox = document.getElementById('div_' + id);

    if (videoBox) {
        videoBox.remove();
    }
}

function toggleTrack(kind, buttonId) {
    const tracks = myStream.getTracks();
    let selectedTrack = null;

    tracks.forEach(function(track) {
        if (track.kind === kind) {
            selectedTrack = track;
        }
    });

    if (!selectedTrack) {
        return;
    }

    selectedTrack.enabled = !selectedTrack.enabled;
    document.getElementById(buttonId).style.background = selectedTrack.enabled ? '' : '#ff1428';
}

function toggleAudio() {
    toggleTrack('audio', 'mic-btn');
}

function toggleVideo() {
    toggleTrack('video', 'cam-btn');
}

function toggleFullScreen() {
    const container = document.getElementById('main-video-container');
    const fsBtn = document.getElementById('fullscreen-btn');

    if (!document.fullscreenElement) {
        container.requestFullscreen().catch(function(err) {
            console.log("Fullscreen error:", err);
        });

        fsBtn.innerHTML = '<i class="fa-solid fa-compress"></i>';
    } else {
        document.exitFullscreen();
        fsBtn.innerHTML = '<i class="fa-solid fa-expand"></i>';
    }
}

document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement) {
        document.getElementById('fullscreen-btn').innerHTML = '<i class="fa-solid fa-expand"></i>';
    }
});

async function toggleScreenShare() {
    const screenBtn = document.getElementById('screen-btn');

    if (!isSharingScreen) {
        try {
            screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: true
            });

            const videoTrack = screenStream.getVideoTracks()[0];

            videoTrack.onended = stopScreenShare;

            replaceVideoTrack(videoTrack);

            isSharingScreen = true;
            screenBtn.style.background = 'var(--hub-purple)';
            screenBtn.innerHTML = '<i class="fa-solid fa-stop-circle"></i>';
        } catch (err) {
            console.error("Screen share error:", err);
        }
    } else {
        stopScreenShare();
    }
}

function stopScreenShare() {
    const videoTrack = myStream.getVideoTracks()[0];

    replaceVideoTrack(videoTrack);

    if (screenStream) {
        screenStream.getTracks().forEach(function(track) {
            track.stop();
        });
    }

    isSharingScreen = false;

    const screenBtn = document.getElementById('screen-btn');
    screenBtn.style.background = '';
    screenBtn.innerHTML = '<i class="fa-solid fa-desktop"></i>';
}

function replaceVideoTrack(newTrack) {
    const localVideo = document.querySelector('#div_local video');

    if (localVideo) {
        const audioTracks = myStream.getAudioTracks();
        const tracks = [];

        tracks.push(newTrack);

        audioTracks.forEach(function(track) {
            tracks.push(track);
        });

        localVideo.srcObject = new MediaStream(tracks);
    }

    if (!peer || !peer.connections) {
        return;
    }

    Object.values(peer.connections).forEach(function(connectionList) {
        connectionList.forEach(function(conn) {
            if (conn.peerConnection) {
                const senders = conn.peerConnection.getSenders();

                const sender = senders.find(function(s) {
                    return s.track && s.track.kind === 'video';
                });

                if (sender) {
                    sender.replaceTrack(newTrack);
                }
            }
        });
    });
}

async function toggleRecording() {
    const recBtn = document.getElementById('rec-btn');

    if (!isRecording) {
        try {
            recordingStream = await navigator.mediaDevices.getDisplayMedia({
                video: true,
                audio: true
            });

            recordedChunks = [];

            mediaRecorder = new MediaRecorder(recordingStream, {
                mimeType: 'video/webm;codecs=vp8,opus'
            });

            mediaRecorder.ondataavailable = function(e) {
                if (e.data.size > 0) {
                    recordedChunks.push(e.data);
                }
            };

            mediaRecorder.onstop = function() {
                recordingStream.getTracks().forEach(function(track) {
                    track.stop();
                });
            };

            mediaRecorder.start();

            isRecording = true;
            recBtn.classList.add('recording-active');
            recBtn.innerHTML = '<i class="fa-solid fa-stop"></i>';
        } catch (err) {
            console.error("Recording error:", err);
        }
    } else {
        stopRecordingProcess();
    }
}

function stopRecordingProcess() {
    if (!mediaRecorder || !isRecording) {
        return;
    }

    mediaRecorder.stop();
    isRecording = false;

    const recBtn = document.getElementById('rec-btn');
    recBtn.classList.remove('recording-active');
    recBtn.innerHTML = '<i class="fa-solid fa-circle"></i>';
}

function saveFile() {
    const blob = new Blob(recordedChunks, {
        type: 'video/webm'
    });

    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = "LinkHub_Record_" + Date.now() + ".webm";
    a.click();
}

function endCallAndCheckRecord() {
    if (isRecording) {
        stopRecordingProcess();

        setTimeout(function() {
            if (confirm("Do you want to save the video?")) {
                saveFile();
            }

            location.reload();
        }, 500);
    } else {
        location.reload();
    }
}
</script>

<?php include 'includes/footer.php'; ?>