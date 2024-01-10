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
                $this->createMessage('success','Your note has been created successfully');
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
    public function getNotes()
    {
        $statement = $this->pdo->prepare("SELECT * FROM notes WHERE user_id = :user_id ORDER BY id DESC");
        $statement->bindValue(':user_id', $this->userId);
        $statement->execute();
        return $statement->fetchAll();
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
            $this->createMessage('success','Your note has been deleted successfully');
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
            $statement = $this->pdo->prepare("UPDATE notes SET title=:title , body=:body WHERE user_id=:user_id AND id=:note_id");
            $statement->bindValue(':user_id', $this->userId);
            $statement->bindValue(':title', $title);
            $statement->bindValue(':body', $body);
            $statement->bindValue(':note_id', $noteId);
            if ($statement->execute()) {
                $this->createMessage('success','your note has been updated successfully');
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
