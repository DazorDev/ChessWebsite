<?php

require_once "chessboard.php";

function isTurn($user) {
}

function capturePiece($pieceID, $color, &$board) {
    for ($i = 0; $i < count($board); $i++) {
        $piece = $board[$i];
        if ($piece->id != $pieceID) continue;
        if ($piece->color != $color) continue;
        array_splice($board, $i, 1);
    }
}

function checkIfChecked($user, $board) {
}

function checkIfPlaceIsFilled($position, $board) {
    return getPieceFromPos($board, $position) != null;
}

function canCapture($piece1, $piece2) {
    return $piece1->color != $piece2->color;
}

function checkIfUnCheck($position, $board) {
}

function isValidMove($piece, $position, &$board) {
    switch ($piece->type) {
        case 'pawn':
            return pawnMove($piece, $position, $board);
        case 'rook':
            return rookMove($piece, $position, $board);
        case 'queen':
            return queenMove($piece, $position, $board);
        case 'king':
            return kingMove($piece, $position, $board);
        case 'bishop':
            return bishopMove($piece, $position, $board);
        case 'knight':
            return knightMove($piece, $position, $board);
    }
}

function moveTo($piece, $move, &$board) {
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            capturePiece($capturePiece->id, $capturePiece->color, $board);
            return true;
        };
        return false;
    }
    return true;
}

function moveToPawn($piece, $move, &$board) {
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            capturePiece($capturePiece->id, $capturePiece->color, $board);
            return true;
        };
        return false;
    }
    return false;
}

function moveToKing($piece, $move, &$board) {
    if (willPutInCheck($piece, $move, $board)) return false;
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            capturePiece($capturePiece->id, $capturePiece->color, $board);
            return true;
        };
        return false;
    }
    return true;
}

function willPutInCheck($piece, $move, $board) {
}

function bishopMove($piece, $move, $board) {
}

function queenMove($piece, $move, &$board) {
    return bishopMove($piece, $move, $board) || rookMove($piece, $move, $board);
}

function kingMove($piece, $move, &$board) {
    if ($piece->x - 1 != $move->x && $piece->x != $move->x && $piece->x + 1 != $move->x) return false;
    if ($piece->y - 1 != $move->y && $piece->y != $move->y && $piece->y + 1 != $move->y) return false;
    return true;
    if (checkIfPlaceIsFilled($move, $board)) {
        $pieceOnMove = getPieceFromPos($board, $move);
        if (canCapture($piece, $pieceOnMove)) {
            capturePiece($piece->id, $piece->color, $board);
            return !willPutInCheck($piece, $move, $board);
        }
        return false;
    }
    return !willPutInCheck($piece, $move, $board);
}

function pawnMove($piece, $move, &$board) {
    if ($piece->color == 'black') {
        if ($piece->y == 6) {
            if ($move->y == $piece->y - 1 || $move->y == $piece->y - 2) return !checkIfPlaceIsFilled($move, $board);
            return false;
        }
        if ($piece->y - 1 != $move->y) return false;
        if ($piece->x != $move->x) {
            if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
                return moveToPawn($piece, $move, $board);
            }
            return false;
        }
        return !checkIfPlaceIsFilled($move, $board);
    }

    if ($piece->y == 1) {
        if ($move->y == $piece->y + 1 || $move->y == $piece->y + 2) return !checkIfPlaceIsFilled($move, $board);
        return false;
    }
    if ($piece->y + 1 != $move->y) return false;
    if ($piece->x != $move->x) {
        if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
            return moveToPawn($piece, $move, $board);
        }
        return false;
    }
    return !checkIfPlaceIsFilled($move, $board);
}

function knightMove($piece, $move, &$board) {
    if (!(($piece->x + 1 == $move->x && $piece->y + 2 == $move->y)
        || ($piece->x - 1 == $move->x && $piece->y + 2 == $move->y)
        || ($piece->x + 1 == $move->x && $piece->y - 2 == $move->y)
        || ($piece->x - 1 == $move->x && $piece->y - 2 == $move->y)
        || ($piece->y + 1 == $move->y && $piece->x + 2 == $move->x)
        || ($piece->y - 1 == $move->y && $piece->x + 2 == $move->x)
        || ($piece->y + 1 == $move->y && $piece->x - 2 == $move->x)
        || ($piece->y - 1 == $move->y && $piece->x - 2 == $move->x)
    )) return false;
    return moveTo($piece, $move, $board);
}

function rookMove($piece, $move, &$board) {
    if ($move->x != $piece->x && $move->y != $piece->y) return;
    if ($move->x != $piece->x) {
        $smallestMove = $move->x;
        for ($x = $move->x; $x != $piece->x; $x = $x > $piece->x ? $x - 1 : $x + 1) {
            if (checkIfPlaceIsFilled($x, $board)) {
                /*if (canCapture($piece, getPiece($x, $board))) {
                    $smallestMove = $x;
                }*/
                $smallestMove = $x - 1;
            }
        }
        return $smallestMove == $move->x;
    }
    $smallestMove = $move->y;
    for ($y = $move->y; $y != $piece->y; $y = $y > $piece->y ? $y - 1 : $y + 1) {
        if (checkIfPlaceIsFilled($y, $board)) {
            if (canCapture($piece, getPiece($piece->x, $y, $board))) {
                $smallestMove = $y;
            }
            $smallestMove = $y > $piece->y ? $y - 1 : $y + 1;
        }
    }
    return $smallestMove == $move->x;
}

function canMove($user, $piece, $position, $board) {
    if (!isTurn($user)) return false;
    if (checkIfPlaceIsFilled($position, $board)) if (!canCapture($piece, getPieceFromPos($board, $position))) return false;
    if (!isValidMove($piece, $position, $board)) return false;
    if (checkIfChecked($user, $board)) {
        if (!checkIfUnCheck($position, $board)) return false;
        return false;
    }
    return true;
}
