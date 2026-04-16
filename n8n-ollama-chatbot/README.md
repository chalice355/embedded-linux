# n8n + Ollama Local Chatbot

WSL2 + Docker Compose로 구현한 로컬 LLM 챗봇 (NVIDIA GPU 지원)

## 환경
- WSL2 Ubuntu
- Docker Engine v29.4.0
- NVIDIA GeForce RTX 5070 (VRAM 12GB)
- CUDA 13.1

## 구성
- **n8n**: 워크플로우 자동화 (포트 5678)
- **Ollama**: 로컬 LLM 서버 (포트 11434)
- **모델**: llama3.1:8b (GPU 가속)

## 실행 방법
```bash
docker compose up -d
docker exec -it ollama ollama pull llama3.1:8b
```
브라우저에서 http://localhost:5678 접속

## n8n Workflow
When chat message received → Basic LLM Chain → Ollama Chat Model (llama3.1:8b)
