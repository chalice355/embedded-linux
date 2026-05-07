# shkim_edge 패키지 - OpenCV 에지 디텍션

- **OS**: Ubuntu 24.04 (Noble Numbat)
- **ROS2**: Jazzy Jalisco
- **OpenCV**: 4.13.0
- **작성일**: 2026-05-07

---

## 목차

1. [개요](#1-개요)
2. [동작 흐름](#2-동작-흐름)
3. [사전 준비 및 의존성 설치](#3-사전-준비-및-의존성-설치)
4. [패키지 구조](#4-패키지-구조)
5. [빌드](#5-빌드)
6. [실행 및 사용법](#6-실행-및-사용법)
7. [키보드 단축키](#7-키보드-단축키)
8. [발행 토픽 상세](#8-발행-토픽-상세)
9. [ROS2 파라미터](#9-ros2-파라미터)
10. [알고리즘 설명](#10-알고리즘-설명)
11. [데모 영상 생성 (카메라 없이)](#11-데모-영상-생성-카메라-없이)
12. [트러블슈팅](#12-트러블슈팅)

---

## 1. 개요

`shkim_edge` 패키지는 USB 카메라(`/dev/video0`)로부터 영상을 받아 OpenCV의 에지 디텍션 알고리즘(Canny / Sobel / Laplacian)을 적용하고, 결과를 ROS2 토픽으로 발행하는 노드입니다.

실행 중 키보드로 알고리즘을 실시간 전환하고 임계값을 조절할 수 있습니다.

```
USB 카메라 (/dev/video0)
        ↓  OpenCV VideoCapture (640×480, 30fps)
  프레임 캡처
        ↓  GaussianBlur → 에지 검출
  ┌──────────────────────────────────────────────┐
  │  선택 가능한 알고리즘                          │
  │  ① Canny      — 얇고 정밀한 경계선            │
  │  ② Sobel      — X/Y 방향 기울기 합산          │
  │  ③ Laplacian  — 2차 미분, 전방향 에지         │
  └──────────────────────────────────────────────┘
        ↓
  ┌──────────────────────────────────────────────┐
  │  ROS2 토픽 발행                               │
  │  /camera/image_raw          (원본 영상)       │
  │  /edge_detection/image      (에지 영상)       │
  │  /edge_detection/combined   (원본+에지 합성)  │
  │  /edge_detection/method     (현재 알고리즘)   │
  │  /edge_detection/edge_pixel_count (에지 픽셀) │
  └──────────────────────────────────────────────┘
        ↓
  cv2.imshow() 로 실시간 3개 창 표시
```

---

## 2. 동작 흐름

```
[1] 카메라 프레임 읽기 (30fps 타이머)
        ↓
[2] /camera/image_raw 발행 (원본 BGR)
        ↓
[3] BGR → Grayscale 변환
        ↓
[4] GaussianBlur (노이즈 제거)
        ↓
[5] 에지 검출 (method 파라미터에 따라)
    ├─ canny     : Canny(blurred, thr1, thr2)
    ├─ sobel     : Sobel X + Sobel Y → 크기 합산
    └─ laplacian : Laplacian → abs
        ↓
[6] 에지 이미지에 정보 텍스트 오버레이
    (알고리즘명, 에지 픽셀 수, 임계값)
        ↓
[7] 원본 | 에지 좌우 합성 이미지 생성
        ↓
[8] ROS2 토픽 4종 발행
        ↓
[9] cv2.imshow() — 3개 창 동시 표시
    ① Original
    ② Edge Detection
    ③ Combined (Original | Edge)
        ↓
[10] 키 입력 감지 → 알고리즘/임계값 실시간 변경
```

---

## 3. 사전 준비 및 의존성 설치

### 3-1. 전체 자동 설치 (권장)

아래 스크립트 한 번으로 ROS2 설치 + 의존성 설치 + 빌드까지 완료됩니다.

```bash
sudo bash ~/Desktop/ws/install_and_build.sh
```

### 3-2. 수동 설치

#### OpenCV 설치

```bash
# pip 없는 경우 먼저 설치
wget -O /tmp/get-pip.py https://bootstrap.pypa.io/get-pip.py
python3 /tmp/get-pip.py --user --break-system-packages

# OpenCV 설치
~/.local/bin/pip install opencv-python --break-system-packages
```

#### cv_bridge 설치

```bash
sudo apt install ros-jazzy-cv-bridge
```

설치 확인:

```bash
python3 -c "import cv2; print('OpenCV:', cv2.__version__)"
# 출력: OpenCV: 4.13.0

source /opt/ros/jazzy/setup.bash
python3 -c "from cv_bridge import CvBridge; print('cv_bridge OK')"
```

---

## 4. 패키지 구조

```
~/ros2_ws/src/shkim_edge/
├── shkim_edge/
│   ├── __init__.py
│   └── edge_detection_node.py   ← 메인 노드 코드
├── test/
│   ├── test_copyright.py
│   ├── test_flake8.py
│   └── test_pep257.py
├── package.xml                   ← 패키지 메타정보 및 ROS 의존성
├── setup.cfg
├── setup.py                      ← 빌드 설정 및 실행 명령 등록
└── resource/
    └── shkim_edge
```

데모 스크립트 (패키지 외부):

```
~/Desktop/ws/
├── install_and_build.sh         ← ROS2 설치 + 빌드 원스텝 스크립트
├── demo_edge_video.py           ← 카메라 없이 데모 영상 생성
└── edge_detection_demo.mp4      ← 생성된 데모 영상 (12초)
```

### package.xml 의존성

| 패키지 | 용도 |
|--------|------|
| `rclpy` | ROS2 Python 클라이언트 |
| `sensor_msgs` | `sensor_msgs/Image` 메시지 |
| `std_msgs` | `String`, `Int32` 메시지 |
| `cv_bridge` | OpenCV ↔ ROS2 이미지 변환 |

---

## 5. 빌드

### 5-1. symlink-install 빌드 (권장)

Python 파일 수정 후 **재빌드 없이 바로 반영**됩니다.

```bash
cd ~/ros2_ws
source /opt/ros/jazzy/setup.bash
colcon build --packages-select shkim_edge --symlink-install
```

빌드 성공 출력:

```
Starting >>> shkim_edge
Finished <<< shkim_edge [2.1s]
Summary: 1 package finished [2.4s]
```

### 5-2. 워크스페이스 환경 로드

빌드 후 반드시 실행해야 노드를 인식합니다.

```bash
source ~/ros2_ws/install/setup.bash
```

`.bashrc`에 추가하면 터미널 시작 시 자동 적용:

```bash
echo "source ~/ros2_ws/install/setup.bash" >> ~/.bashrc
source ~/.bashrc
```

---

## 6. 실행 및 사용법

### 6-1. 기본 실행

```bash
source ~/ros2_ws/install/setup.bash
ros2 run shkim_edge edge_detection_node
```

정상 실행 시 출력:

```
[INFO] [edge_detection_node]: EdgeDetectionNode started
[INFO] [edge_detection_node]: Camera: /dev/video0 | 640x480 | Method: canny
[INFO] [edge_detection_node]: Canny thresholds: 50/150 | Blur ksize: 5
[INFO] [edge_detection_node]: Running... frame=90 | edge_pixels=12453 | method=canny
```

화면: OpenCV 창 3개가 열리며 실시간 영상 표시

| 창 이름 | 내용 |
|---------|------|
| `Original` | USB 카메라 원본 영상 |
| `Edge Detection` | 에지 결과 + 정보 오버레이 |
| `Combined (Original \| Edge)` | 원본과 에지를 좌우로 나란히 |

### 6-2. 파라미터 변경 실행

```bash
ros2 run shkim_edge edge_detection_node --ros-args \
  -p camera_index:=0 \
  -p method:=sobel \
  -p canny_threshold1:=30 \
  -p canny_threshold2:=90 \
  -p blur_ksize:=7 \
  -p display:=true
```

### 6-3. 토픽 확인 (별도 터미널)

```bash
source ~/ros2_ws/install/setup.bash

# 활성 토픽 목록
ros2 topic list

# 현재 알고리즘 확인
ros2 topic echo /edge_detection/method

# 에지 픽셀 수 실시간 확인
ros2 topic echo /edge_detection/edge_pixel_count

# 발행 주기 확인 (30Hz 근처여야 정상)
ros2 topic hz /camera/image_raw
```

---

## 7. 키보드 단축키

영상 창이 **포커스된 상태**에서 아래 키가 동작합니다.

| 키 | 동작 |
|----|------|
| `c` | **Canny** 모드로 전환 |
| `s` | **Sobel** 모드로 전환 |
| `l` | **Laplacian** 모드로 전환 |
| `+` 또는 `=` | Canny 임계값 +10 증가 (thr1, thr2 동시) |
| `-` | Canny 임계값 -10 감소 (thr1, thr2 동시) |
| `q` | 노드 종료 |

> **임계값 조절 범위**: thr1 0~500, thr2 0~500  
> Canny 모드에서만 임계값이 화면에 표시됩니다.

---

## 8. 발행 토픽 상세

| 토픽 이름 | 메시지 타입 | 내용 |
|-----------|-----------|------|
| `/camera/image_raw` | `sensor_msgs/Image` | USB 카메라 원본 영상 (bgr8) |
| `/edge_detection/image` | `sensor_msgs/Image` | 에지 결과 + 정보 오버레이 (bgr8) |
| `/edge_detection/combined` | `sensor_msgs/Image` | 원본·에지 좌우 합성 (bgr8, 폭 2배) |
| `/edge_detection/method` | `std_msgs/String` | 현재 알고리즘 문자열 (`"canny"` 등) |
| `/edge_detection/edge_pixel_count` | `std_msgs/Int32` | 에지로 검출된 픽셀 수 |

---

## 9. ROS2 파라미터

| 파라미터 | 기본값 | 설명 |
|---------|--------|------|
| `camera_index` | `0` | 카메라 장치 번호 (`/dev/video0`) |
| `method` | `"canny"` | 알고리즘 선택: `canny` / `sobel` / `laplacian` |
| `canny_threshold1` | `50` | Canny 하한 임계값 |
| `canny_threshold2` | `150` | Canny 상한 임계값 (보통 thr1 × 3) |
| `blur_ksize` | `5` | GaussianBlur 커널 크기 (홀수여야 함) |
| `display` | `true` | cv2.imshow 창 표시 여부 |

---

## 10. 알고리즘 설명

### 공통 전처리: GaussianBlur

모든 알고리즘 적용 전에 가우시안 블러로 노이즈를 제거합니다.

```python
blurred = cv2.GaussianBlur(gray, (blur_ksize, blur_ksize), 0)
```

`blur_ksize`가 클수록 노이즈 제거 효과가 크지만 세밀한 에지가 사라질 수 있습니다.

---

### ① Canny (기본값)

```python
edges = cv2.Canny(blurred, thr1, thr2)
```

- **원리**: 기울기 계산 → Non-maximum suppression → 이중 임계값(Hysteresis)
- **결과**: 얇고 연속적인 단일 픽셀 폭 경계선
- **장점**: 노이즈에 강하고 가장 정밀한 에지
- **조절**: `+` / `-` 키로 임계값 실시간 변경
  - `thr1` 낮을수록 → 약한 에지도 포함 (에지 많아짐)
  - `thr2` 높을수록 → 강한 에지만 남음 (에지 적어짐)

| thr1 / thr2 | 효과 |
|------------|------|
| 20 / 60 | 에지 많음, 노이즈 포함 가능 |
| **50 / 150** | **기본값, 균형 잡힌 결과** |
| 100 / 300 | 강한 에지만, 세밀한 경계 사라짐 |

---

### ② Sobel

```python
sx = cv2.Sobel(blurred, cv2.CV_64F, 1, 0, ksize=3)  # X 방향
sy = cv2.Sobel(blurred, cv2.CV_64F, 0, 1, ksize=3)  # Y 방향
edges = np.sqrt(sx**2 + sy**2)
```

- **원리**: X/Y 방향 1차 미분 필터로 기울기 크기 계산
- **결과**: Canny보다 굵은 에지, 그라디언트 세기가 밝기로 표현
- **장점**: 에지의 방향성과 강도를 파악할 수 있음

---

### ③ Laplacian

```python
lap = cv2.Laplacian(blurred, cv2.CV_64F)
edges = np.abs(lap)
```

- **원리**: 2차 미분으로 밝기 변화의 변곡점 검출
- **결과**: 모든 방향의 에지를 동시에 검출
- **특징**: 노이즈에 민감하므로 블러 전처리가 중요

---

### 알고리즘 비교

| 항목 | Canny | Sobel | Laplacian |
|------|-------|-------|-----------|
| 에지 두께 | 1픽셀 (얇음) | 수 픽셀 (굵음) | 수 픽셀 |
| 노이즈 강도 | 강함 | 보통 | 약함 |
| 방향성 | 없음 | X/Y 분리 가능 | 없음 |
| 임계값 조절 | 가능 (`+`/`-`) | 없음 | 없음 |
| 적합한 용도 | 윤곽선 검출 | 기울기 분석 | 전방향 에지 |

---

## 11. 데모 영상 생성 (카메라 없이)

카메라가 없어도 `demo_edge_video.py`로 알고리즘 동작 확인이 가능합니다.

```bash
export PATH="$HOME/.local/bin:$PATH"
python3 ~/Desktop/ws/demo_edge_video.py
```

출력: `~/Desktop/ws/edge_detection_demo.mp4` (12초, 1280×480, 30fps)

| 구간 | 알고리즘 | 색상 라벨 |
|------|---------|---------|
| 0 ~ 4초 | Canny | 노란색 |
| 4 ~ 8초 | Sobel | 하늘색 |
| 8 ~ 12초 | Laplacian | 보라색 |

---

## 12. 트러블슈팅

### `Cannot open /dev/video0`

카메라 연결 확인:

```bash
ls /dev/video*          # 카메라 장치 목록
v4l2-ctl --list-devices # 상세 정보
```

다른 번호로 실행:

```bash
ros2 run shkim_edge edge_detection_node --ros-args -p camera_index:=2
```

### `ModuleNotFoundError: No module named 'cv2'`

OpenCV 설치:

```bash
~/.local/bin/pip install opencv-python --break-system-packages
```

### `cv_bridge` 없음

```bash
sudo apt install ros-jazzy-cv-bridge
```

### 창이 뜨지 않음 (headless 환경)

`display` 파라미터를 false로 설정하고 토픽으로만 확인:

```bash
ros2 run shkim_edge edge_detection_node --ros-args -p display:=false
ros2 topic echo /edge_detection/edge_pixel_count
```

### 에지가 너무 많거나 적음

실행 중 키보드로 조절:

```
너무 많음 → + 키 (임계값 증가)
너무 적음 → - 키 (임계값 감소)
노이즈 심함 → Canny 모드(c) 사용
```

또는 재실행 시 파라미터로 설정:

```bash
ros2 run shkim_edge edge_detection_node --ros-args \
  -p canny_threshold1:=80 \
  -p canny_threshold2:=240 \
  -p blur_ksize:=7
```
