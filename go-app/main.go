package main

import (
	"encoding/json"
	"net/http"
	"strconv"
)

// City represents a city with a name and country.
type City struct {
	Name    string `json:"name"`
	Country string `json:"country"`
}

// citiesData simulates a database of cities.
var citiesData = map[int]City{
	1: {Name: "New York", Country: "USA"},
	2: {Name: "London", Country: "UK"},
	3: {Name: "Paris", Country: "France"},
	4: {Name: "Tokyo", Country: "Japan"},
	5: {Name: "Sydney", Country: "Australia"},
}

// getCitiesHandler returns a list of cities.
func getCitiesHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(citiesData)
}

func getCityHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	id, err := strconv.Atoi(r.PathValue("id"))
	if err != nil {
		http.Error(w, "Invalid ID", http.StatusBadRequest)
		return
	}
	city, ok := citiesData[id]
	if !ok {
		http.Error(w, "City not found", http.StatusNotFound)
		return
	}
	json.NewEncoder(w).Encode(city)
}

func main() {
	http.HandleFunc("/cities", getCitiesHandler)
	http.HandleFunc("/cities/{id}", getCityHandler)
	http.ListenAndServe(":8080", nil)
}
