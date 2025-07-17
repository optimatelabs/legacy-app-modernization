#!/bin/bash

set -ex

PROJECT_ID="legacy-app-modernization"
PROJECT_NUMBER=$(gcloud projects describe ${PROJECT_ID} --format='value(projectNumber)')
REGION="europe-west1"
ZONE="europe-west1-b"

gcloud config set project ${PROJECT_ID}
gcloud config set run/region ${REGION}


gcloud services enable apigateway.googleapis.com \
    servicemanagement.googleapis.com \
    servicecontrol.googleapis.com \
    run.googleapis.com \
    cloudbuild.googleapis.com \
    artifactregistry.googleapis.com \
    cloudbuild.googleapis.com \
    run.googleapis.com

API_GATEWAY_SA="api-gateway-sa"
API_GATEWAY_SA_EMAIL="${API_GATEWAY_SA}@${PROJECT_ID}.iam.gserviceaccount.com"

gcloud iam service-accounts create ${API_GATEWAY_SA} --project=${PROJECT_ID} --display-name="API Gateway Service Account"
gcloud iam service-accounts add-iam-policy-binding ${API_GATEWAY_SA_EMAIL} \
    --member user:fabrizio@optimatelabs.com \
    --role roles/iam.serviceAccountUser

gcloud run deploy legacy-php-app --project ${PROJECT_ID} \
    --source php-app \
    --platform managed \
    --region ${REGION} \
    --allow-unauthenticated \
    --port 8080 \
    --cpu 1 \
    --memory 256Mi \
    --concurrency 80 \
    --timeout 300 \
    --clear-env-vars

gcloud run deploy new-go-app --project ${PROJECT_ID} \
    --source go-app \
    --platform managed \
    --region ${REGION} \
    --allow-unauthenticated \
    --port 8080 \
    --cpu 1 \
    --memory 256Mi \
    --concurrency 80 \
    --timeout 300 \
    --clear-env-vars

PHP_BACKEND_URL=$(gcloud run services describe legacy-php-app --format="value(status.address.url)")
GO_BACKEND_URL=$(gcloud run services describe new-go-app --format="value(status.address.url)")
sed -e "s|PHP_BACKEND_URL|${PHP_BACKEND_URL}|" -e "s|GO_BACKEND_URL|${GO_BACKEND_URL}|" openapi.yaml.template > openapi.yaml

API="new-api-v2"
API_CONFIG="new-api-v2-config"
API_GATEWAY="new-api-gateway"
gcloud api-gateway apis create ${API} --project=${PROJECT_ID}
gcloud api-gateway api-configs create ${API_CONFIG} \
    --api=${API} \
    --openapi-spec=openapi.yaml \
    --project=${PROJECT_ID} \
    --backend-auth-service-account=api-gateway-sa@legacy-app-modernization.iam.gserviceaccount.com

gcloud api-gateway gateways create ${API_GATEWAY} \
  --api=${API} \
  --api-config=${API_CONFIG} \
  --location=${REGION} \
  --project=${PROJECT_ID}
  