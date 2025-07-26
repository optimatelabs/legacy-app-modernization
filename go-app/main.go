package main

import (
	"encoding/json"
	"net/http"
	"strconv"
)

// Course represents an e-learning course with a title and instructor.
type Course struct {
	Title      string `json:"title"`
	Instructor string `json:"instructor"`
}

// coursesData simulates a database of e-learning courses.
var coursesData = map[int]Course{
	1: {Title: "Go Programming Masterclass", Instructor: "John Doe"},
	2: {Title: "React - The Complete Guide", Instructor: "Jane Smith"},
	3: {Title: "Python for Data Science", Instructor: "Peter Jones"},
	4: {Title: "Machine Learning A-Z", Instructor: "Alice Brown"},
}

// getCoursesHandler returns a list of courses.
func getCoursesHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(coursesData)
}

func getCourseHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	id, err := strconv.Atoi(r.PathValue("id"))
	if err != nil {
		http.Error(w, "Invalid ID", http.StatusBadRequest)
		return
	}
	course, ok := coursesData[id]
	if !ok {
		http.Error(w, "Course not found", http.StatusNotFound)
		return
	}
	json.NewEncoder(w).Encode(course)
}

func main() {
	http.HandleFunc("/courses", getCoursesHandler)
	http.HandleFunc("/courses/{id}", getCourseHandler)
	http.ListenAndServe(":8080", nil)
}
