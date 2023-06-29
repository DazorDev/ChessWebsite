// Add event listeners for drag and drop functionality
document.addEventListener('DOMContentLoaded', () => {
    const pieces = document.querySelectorAll('.piece');

    pieces.forEach((piece) => {
        piece.addEventListener('dragstart', dragStart);
        piece.addEventListener('dragend', dragEnd);
    });
});

function dragStart(e) {
    e.dataTransfer.setData('text/plain', e.target.id);
    e.target.style.opacity = '0.5';
}

function dragEnd(e) {
    e.target.style.opacity = '1';
}

// Dynamically add pieces to existing squares on the chessboard
document.addEventListener('DOMContentLoaded', () => {
    const squares = document.querySelectorAll('.chessboard .square');
    const piecePositions = [
        // Define the positions where you want to place the pieces
        //Add all of the pawns of team 1
        { row: 1, col: 0, image: './pieces/piece2.png' },
        { row: 1, col: 1, image: './pieces/piece2.png' },
        { row: 1, col: 2, image: './pieces/piece2.png' },
        { row: 1, col: 3, image: './pieces/piece2.png' },
        { row: 1, col: 4, image: './pieces/piece2.png' },
        { row: 1, col: 5, image: './pieces/piece2.png' },
        { row: 1, col: 6, image: './pieces/piece2.png' },
        { row: 1, col: 7, image: './pieces/piece2.png' },
        //Add all of the pawns of team 2
        { row: 6, col: 0, image: './pieces/piece2.png' },
        { row: 6, col: 1, image: './pieces/piece2.png' },
        { row: 6, col: 2, image: './pieces/piece2.png' },
        { row: 6, col: 3, image: './pieces/piece2.png' },
        { row: 6, col: 4, image: './pieces/piece2.png' },
        { row: 6, col: 5, image: './pieces/piece2.png' },
        { row: 6, col: 6, image: './pieces/piece2.png' },
        { row: 6, col: 7, image: './pieces/piece2.png' },

        //Add all of the knights of team 1
        { row: 0, col: 0, image: './pieces/piece1.png' },
        { row: 0, col: 7, image: './pieces/piece1.png' },
        //Add all of the knights of team 2
        { row: 7, col: 0, image: './pieces/piece1.png' },
        { row: 7, col: 7, image: './pieces/piece1.png' },


        { row: 0, col: 1, image: './pieces/piece3.png' },
        { row: 0, col: 6, image: './pieces/piece3.png' },
        { row: 7, col: 1, image: './pieces/piece3.png' },
        { row: 7, col: 6, image: './pieces/piece3.png' },

        { row: 0, col: 2, image: './pieces/piece4.png' },
        { row: 0, col: 5, image: './pieces/piece4.png' },
        { row: 7, col: 2, image: './pieces/piece4.png' },
        { row: 7, col: 5, image: './pieces/piece4.png' },

        { row: 0, col: 3, image: './pieces/piece5.png' },
        { row: 0, col: 4, image: './pieces/piece6.png' },
        { row: 7, col: 3, image: './pieces/piece5.png' },
        { row: 7, col: 4, image: './pieces/piece6.png' },
    ];

    piecePositions.forEach((position) => {
        const square = squares[position.row * 8 + position.col];
        const piece = document.createElement('img');
        piece.classList.add('piece');
        piece.src = position.image;
        piece.id = `piece-${position.row}-${position.col}`;
        piece.draggable = true;

        // Size down the image to fit within the square
        piece.addEventListener('load', () => {
            const squareSize = square.clientWidth;
            const pieceSize = Math.min(squareSize, square.clientHeight);
            piece.style.width = `${pieceSize}px`;
            piece.style.height = `${pieceSize}px`;
        });

        square.appendChild(piece);
    });
});

// Add event listeners for drag and drop functionality
document.addEventListener('DOMContentLoaded', () => {
    const squares = document.querySelectorAll('.chessboard .square');
    const pieces = document.querySelectorAll('.chessboard .piece');

    pieces.forEach((piece) => {
        piece.addEventListener('dragstart', dragStart);
        piece.addEventListener('dragend', dragEnd);
    });

    squares.forEach((square) => {
        square.addEventListener('dragenter', dragEnter);
        square.addEventListener('dragover', dragOver);
        square.addEventListener('dragleave', dragLeave);
        square.addEventListener('drop', drop);
    });
});

let draggedPiece = null;
let dropAllowed = false;

function dragStart(e) {
    e.dataTransfer.setData('text/plain', e.target.id);
    e.target.style.opacity = '0.5';
    draggedPiece = e.target;
}

function dragEnd(e) {
    e.target.style.opacity = '1';
    draggedPiece = null;

    const hoveredSquare = document.querySelector('.chessboard .square.hovered');
    if (hoveredSquare) {
        hoveredSquare.classList.remove('hovered');
        const centeredSquare = document.querySelector('.chessboard .square.centered');
        if (centeredSquare) centeredSquare.classList.remove('centered');
    }
}

function dragEnter(e) {
    e.preventDefault();

    if (draggedPiece) {
        const hoveredSquare = document.querySelector('.chessboard .square.hovered');
        if (hoveredSquare) hoveredSquare.classList.remove('hovered');

        if (isDropAllowed(e.target)) {
            e.target.classList.add('hovered');
            dropAllowed = true;
        } else {
            dropAllowed = false;
        }
    }
}

function dragOver(e) {
    e.preventDefault();
}

function dragLeave(e) {
    e.preventDefault();

    if (draggedPiece && dropAllowed) {
        e.target.classList.remove('hovered');
    }
}

function drop(e) {
    e.preventDefault();
    console.log("hello")
    if (draggedPiece && dropAllowed) {
        const targetSquare = e.target;
        const centeredSquare = document.querySelector('.chessboard .square.centered');
        if (centeredSquare) centeredSquare.classList.remove('centered');
        console.log("logged parent")
        if (!isDropAllowed(targetSquare)) {
            return;
        }
        const clonedPiece = draggedPiece.cloneNode(true); // Clone the dragged piece
        targetSquare.appendChild(clonedPiece); // Append the clone to the target square
    }
}

function isDropAllowed(square) {
    // Get previous board state before the move
    const previousBoard = getBoardState();
    // Temporarily move the piece to the target square
    if (!square.contains(draggedPiece)) square.appendChild(draggedPiece);

    // Get the current board state after the move
    const currentBoard = getBoardState();

    // Validate the move based on the previous and current board states
    const moveValid = validateMove(previousBoard, currentBoard);

    return moveValid;
}

function getBoardState() {
    const boardState = [];
    const squares = document.querySelectorAll('.chessboard .square');

    squares.forEach((square) => {
        const piece = square.querySelector('.piece');
        boardState.push(piece ? piece.dataset.piece : null);
    });

    return boardState;
}

function validateMove(previousBoard, currentBoard) {
    return false
}