<?php

require 'matchmaking.php';
require 'chessboard.php';
require 'movement.php';
require 'connect.php';
require 'Position.php';

function connect() {
    $data = getJsonBody();
    if ($data['user'] == null) {
        echo json_encode(array('success' => false));
        return;
    }
    //Checking if the User is inside of a game, if not then start the search / get results from the search
    if ($data['match'] == null) {
        startSearch($data);
        //echo json_encode(array('success' => true));
        return;
    }
    echo json_encode(array('success' => false));
}

function update() {
    sendUpdate(getJsonBody()['match']);
}

function getJsonBody() {
    return json_decode(file_get_contents("php://input"), true);
}

function move() {
    $data = getJsonBody();
    $gameData = getGameData($data['match']);
    $piece = getPiece($gameData, $data['id'], $data['color']);
    if (!isValidMove($piece, new Position(intval($data['move'][0]), intval($data['move'][1])), $gameData, $data['match'], $data['user'])) {
        echo json_encode(array('success' => true, 'move' => false, getPiece($gameData, $data['id'], $data['color']), new Position($data['move'][0], $data['move'][1]), $gameData));
        return;
    }
    $piece->x = intval($data['move'][0]);
    $piece->y = intval($data['move'][1]);
    updateGameData($data['user'], $data['match'], $gameData);
    echo json_encode(array('success' => true, 'move' => true));
}

function disconnect() {
    endMatch(getJsonBody());
}

function main() {
    global $connection;
    if (!isset($_GET['method'])) {
        echo 'oye bruv you idiot';
        return;
    }
    $method = $_GET['method'];
    switch ($method) {
        case 'connect':
            connect();
            break;
        case 'update':
            update();
            break;
        case 'move':
            move();
            break;
        case 'disconnect':
            disconnect();
            break;
    }
    mysqli_close($connection);
}

main();
