<?php

class Note
{
    public PDO $pdo;
    public int $userId;
    public array $errors = [];
    public array $requiredFields = [];

    public function __construct(PDO $pdo, int $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    /**
     * create note method
     * 
     * title is optional (default is current date and time)
     * title shouldn't exceed 40 letters
     * @param string $title
     * @param string $body
     * 
     * @return bool
     */
    public function createNote(string $title, string $body): bool
    {
        // validating required fields
        $this->requiredFields = ["note body" => $body];
        Validation::validateRequiredFields($this->requiredFields);

        // validating title
        Validation::validateNoteTitle($title);

        if (Validation::$errors == []) {
            $statement = $this->pdo->prepare("INSERT INTO notes (user_id,title,body) VALUES (:user_id,:title,:body)");
            $statement->bindValue(':user_id', $this->userId);
            $statement->bindValue(':title', $title);
            $statement->bindValue(':body', $body);
            if ($statement->execute()) {
                $this->createMessage('success', 'Your note has been created successfully');
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * get user notes method
     */
    public function getNotes(int $itemsPerPage = 20, int $page = 1, string $sortBy = 'created_at', string $sortType = 'DESC', string $fromDate = '2024-1-11', string $toDate = '2024-4-1')
    {
        $allowedSortColumns = ['created_at', 'updated_at', 'title'];
        $allowedSortTypes = ['ASC', 'DESC'];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortType, $allowedSortTypes)) {
            $sortType = 'DESC';
        }

        $offset = ($page - 1) * $itemsPerPage;
        $statement = $this->pdo->prepare("SELECT * FROM notes WHERE user_id = :user_id AND created_at>=:from_date AND created_at<=:to_date ORDER BY $sortBy $sortType LIMIT $itemsPerPage OFFSET $offset");
        $statement->bindValue(':user_id', $this->userId);
        $statement->bindValue(':from_date', $fromDate);
        $statement->bindValue(':to_date', $toDate);
        $statement->execute();
        return $statement->fetchAll();
    }


    /**
     * count number of notes method
     */
    public function countNotesNumber()
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) AS notesCount From notes WHERE user_id = :user_id");
        $statement->bindValue(':user_id', $this->userId);
        $statement->execute();
        return $statement->fetchColumn();
    }
    /**
     * get note by id method
     */

    public function getNoteById(int $noteId)
    {
        $statement = $this->pdo->prepare("SELECT * FROM notes WHERE user_id = :user_id AND id = :note_id LIMIT 1");
        $statement->bindValue(':user_id', $this->userId);
        $statement->bindValue(':note_id', $noteId);
        $statement->execute();
        return $statement->fetch();
    }

    /**
     * delete note method
     */

    public function deleteNote(int $noteId)
    {
        $statement = $this->pdo->prepare("DELETE FROM notes WHERE user_id=:user_id AND id=:note_id LIMIT 1");
        $statement->bindValue(':user_id', $this->userId);
        $statement->bindValue(':note_id', $noteId);
        if ($statement->execute()) {
            $this->createMessage('success', 'Your note has been deleted successfully');
            return true;
        } else {
            return false;
        }
    }

    private function createMessage(string $type, string $message)
    {
        session_start();
        $_SESSION[$type] = $message;
        session_write_close();
    }

    /**
     * Update note method
     */
    public function updateNote(int $noteId, string $title, string $body): bool
    {
        // validating required fields
        $this->requiredFields = ["note body" => $body];
        Validation::validateRequiredFields($this->requiredFields);

        // validating title
        Validation::validateNoteTitle($title);

        if (Validation::$errors == []) {
            $updatedAt = date('Y-m-d H:i:s');
            $statement = $this->pdo->prepare("UPDATE notes SET title=:title , body=:body ,updated_at=now() WHERE user_id=:user_id AND id=:note_id");
            $statement->bindValue(':user_id', $this->userId);
            $statement->bindValue(':title', $title);
            $statement->bindValue(':body', $body);
            $statement->bindValue(':note_id', $noteId);
            if ($statement->execute()) {
                $this->createMessage('success', 'your note has been updated successfully');
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * generate excerpt method
     */
    public function genrateExcerpt(string $noteBody)
    {
        if (strlen($noteBody) <= 50) {
            return $noteBody;
        } else {
            $excerpt = substr($noteBody, 0, 50);
            return $excerpt . '...';
        }
    }
}
