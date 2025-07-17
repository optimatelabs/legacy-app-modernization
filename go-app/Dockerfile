# Use the official Go image as the base image
FROM golang:1.24.4-alpine

# Set the working directory inside the container
WORKDIR /app

# Copy the Go application source code into the container
COPY . .

# Build the Go application
# CGO_ENABLED=0 is used to disable CGO, which creates a statically linked binary
# This is often preferred for Go applications in Docker containers to avoid dependencies
# -o /app/main specifies the output binary name and path
RUN CGO_ENABLED=0  go build -o /app/main .

# Expose the port that the application will listen on
# Cloud Run typically expects applications to listen on the port specified by the PORT environment variable
EXPOSE 8080

# Define the command to run the application
# This will execute the built binary when the container starts
CMD ["/app/main"]
