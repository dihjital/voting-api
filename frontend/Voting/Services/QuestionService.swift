//
//  QuestionService.swift
//  Voting
//
//  Created by Szabolcs Tóth on 17.11.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import Foundation

struct QuestionService {
    
    enum QuestionServiceError: Error {
        case failed
        case failedToDecode
        case invalidStatusCode
    }
    
    func fetchQuestions() async throws -> [Question] {
        guard let url = URL(string: "http://localhost:8000/questions") else {
            return []
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let response = response as? HTTPURLResponse,
              response.statusCode == 200 else {
            throw QuestionServiceError.invalidStatusCode
        }
        
       let decodeData = try JSONDecoder().decode([Question].self, from: data)
        return decodeData
    }
    
    func fetchVotes(questionID: Int) async throws -> [Vote] {
        guard let url = URL(string: "http://localhost:8000/questions/\(questionID)/votes") else {
            return []
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let response = response as? HTTPURLResponse,
              response.statusCode == 200 else {
            throw QuestionServiceError.invalidStatusCode
        }
        
       let decodeData = try JSONDecoder().decode([Vote].self, from: data)
        return decodeData
    }
}
