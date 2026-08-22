FROM nvidia/cuda:12.4.1-runtime-ubuntu22.04

WORKDIR /app

COPY requirements.txt

RUN apt update && apt install -y python3 python3-pip

RUN pip3 install -r requirements.txt

COPY app.py .

CMD [
    "uvicorn",
    "app:app",
    "--host",
    "0.0.0.0",
    "--port",
    "8000"
]